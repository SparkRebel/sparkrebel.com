<?php

namespace PW\FeatureBundle\Command;

use PW\FeatureBundle\Document\Feature,
    PW\UserBundle\Document\User,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * Create or update an active feature record for a board
 */
class FeatureBoardCommand extends ContainerAwareCommand
{

    /**
     * document manager placeholder instance
     */
    protected $dm;

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('feature:board')
            ->setDescription('Feature a board')
            ->setDefinition(array(
                new InputArgument(
                    'board',
                    InputArgument::REQUIRED,
                    'The board id'
                )
            ));
    }

    /**
     * execute
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $boardId = $input->getArgument('board');

        $this->dm = $this->getContainer()
            ->get('doctrine_mongodb.odm.document_manager');
        $this->featureRepo = $this->dm->getRepository('PWFeatureBundle:Feature');
        $this->boardRepo = $this->dm->getRepository('PWBoardBundle:Board');

        $board = $this->boardRepo->find($boardId);
        if (!$board) {
            throw new \Exception("Board $boardId doesn't exist");
        }

        $conditions = array(
            'target.$id' => new \MongoId($boardId),
            'isActive' => true
        );
        $feature = $this->featureRepo->findOneBy($conditions);
        if ($feature) {
            $action = 'updated';
        } else {
            $action = 'created';
            $feature = new Feature();
            $feature->setIsActive(true);
            $feature->setTarget($board);
        }

        $this->dm->persist($feature);
        $this->dm->flush();

        $output->write("$action feature record for borad $boardId\n");
    }

}
