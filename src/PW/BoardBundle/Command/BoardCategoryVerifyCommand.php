<?php

namespace PW\BoardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * BoardCategoryVerify
 */
class BoardCategoryVerifyCommand extends ContainerAwareCommand
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
            ->setName('board:category:verify')
            ->setDescription('attach a category to category-less boards')
            ->setDefinition(array(
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
        $catRepo = $this->dm->getRepository('PWCategoryBundle:Category');
        $boardRepo = $this->dm->getRepository('PWBoardBundle:Board');

        $defaultCategory = $catRepo->findOneByName('Gifts & Wish Lists');

        if (empty($defaultCategory)) {
            throw new \Exception("Can't load default category");
        }

        $boards = $boardRepo->findBy(array(
           'isSystem' => false,
           'category' => null
        ));

        if (empty($boards) || $boards->count() == 0) {
            throw new \Exception("no boards withou categories found");
        }

        $count = $boards->count();

        foreach ($boards as $board) {
            $board->setCategory($defaultCategory);
            $this->dm->persist($board);
        }

        $this->dm->flush();

        $output->write("fixed $count boards \n");
    }

}
