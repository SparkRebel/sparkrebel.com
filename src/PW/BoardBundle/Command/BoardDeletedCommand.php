<?php

namespace PW\BoardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * BoardDeletedCommand
 */
class BoardDeletedCommand extends ContainerAwareCommand
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
            ->setName('board:deleted')
            ->setDescription('Cleanup after a board has been deleted')
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
        /* @var $followManager \PW\UserBundle\Model\FollowManager */
        /* @var $postManager \PW\PostBundle\Model\PostManager */
        $output->writeln("<info>[" . date('Y-m-d H:i:s') . "] starting command board:deleted</info>");
        $this->dm = $this->getContainer()
            ->get('doctrine_mongodb.odm.document_manager');
        $boardRepo = $this->dm->getRepository('PWBoardBundle:Board');

        $boardId = $input->getArgument('board');
        $board = $boardRepo->find($boardId);
$output->writeln("<info>1</info>");
        $followManager = $this->getContainer()->get('pw_user.follow_manager');
        $boardFollowers = $followManager->getRepository()
            ->findFollowersByBoard($board)
            ->getQuery()->execute();
$output->writeln("<info>2</info>");
        $followManager->deleteAll($boardFollowers, $board->getDeletedBy(), false);
$output->writeln("<info>3</info>");
        $postManager = $this->getContainer()->get('pw_post.post_manager');
        $boardPosts  = $postManager->getRepository()
            ->findByBoard($board)
            ->getQuery()->execute();
$output->writeln("<info>4</info>");
        $postManager->deleteAll($boardPosts, $board->getDeletedBy());
        $output->writeLn("Processed board $boardId");
        $output->writeln("<info>[" . date('Y-m-d H:i:s') . "] ending command board:deleted</info>");
    }
}
