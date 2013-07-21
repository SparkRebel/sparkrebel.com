<?php

namespace PW\CategoryBundle\Command;

use PW\CategoryBundle\Document\Category,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 */
class CategoryAssignBoardCommand extends ContainerAwareCommand
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
            ->setName('category:assign-board')
            ->setDescription('Assigns board to given category')
            ->setDefinition(array(
                new InputArgument(
                    'category',
                    InputArgument::REQUIRED,
                    'category name or id'
                ),
                new InputArgument(
                    'boardId',
                    InputArgument::REQUIRED,
                    'id of board'
                ),
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

        $this->dm = $this->getContainer()
            ->get('doctrine_mongodb.odm.document_manager');
        $repo = $this->dm->getRepository('PWCategoryBundle:Category');
        $boardRepo = $this->dm->getRepository('PWBoardBundle:Board');

        $category = $input->getArgument('category');
        $boardId = $input->getArgument('boardId');


        $conditions = array(
            '$or' => array(
                array('id' => $category),
                array('id' => new \MongoId($category)),
                array('name' => $category),
            )
        );
        
        if ($repo->findBy($conditions)->count() > 1) {
            throw new  \Exception("{$name} is ambigous");
        }

        $category = $repo->findOneBy($conditions);

        if (!$category) {
            throw new \Exception("Can't find category");
        }

        $board = $boardRepo->find($boardId);

        if (!$board) {
            throw new \Exception("Can't find board");
        }

        $board->setCategory($category);
        $board->setAdminScore(1);        
        $this->dm->persist($board);
        $this->dm->flush();

        $output->writeln("<question>Board {$board->getName()} assigned to {$category->getName()}!</question>");

    }
}
