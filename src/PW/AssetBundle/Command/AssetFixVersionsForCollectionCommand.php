<?php

namespace PW\AssetBundle\Command;

use PW\ApplicationBundle\Resources\ProgressBar,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\ArrayInput,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\NullOutput,
    Symfony\Component\Console\Output\OutputInterface;


class AssetFixVersionsForCollectionCommand extends ContainerAwareCommand
{

    protected $dm;
    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('asset:version:fix-board')
            ->setDescription('Fixes image sizes for board')     
            ->setDefinition(array(
                new InputArgument(
                    'board',
                    InputArgument::REQUIRED,
                    'The board $id'
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
        $boardRepo = $this->dm->getRepository('PWBoardBundle:Board');

        $boardId = $input->getArgument('board');
        $board = $boardRepo->find($boardId);
                  

        $postManager = $this->getContainer()->get('pw_post.post_manager');
        $boardPosts  = $postManager->getRepository()
            ->findByBoard($board)
            ->field('image')->prime(true)
            ->getQuery()->execute();

        foreach ($boardPosts as $post) {
            $this->getContainer()->get('pw.event')->requestJob('asset:version '. $post->getImage()->getId());
        }    
        $output->writeLn("Processed board $boardId");
    }
}
