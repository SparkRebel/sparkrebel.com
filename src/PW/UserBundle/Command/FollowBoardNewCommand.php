<?php

namespace PW\UserBundle\Command;

use PW\UserBundle\Document\Follow,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * When a new board is created:
 * if anyone if following the creating user, automatically make him follow board
 */
class FollowBoardNewCommand extends ContainerAwareCommand
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
            ->setName('follow:board:new')
            ->setDescription('make all users that follow creating user follow new board')
            ->setDefinition(array(
                new InputArgument(
                    'user',
                    InputArgument::REQUIRED,
                    'The creating user $id'
                ),
                new InputArgument(
                    'board',
                    InputArgument::REQUIRED,
                    'The board $id'
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
        $userId = $input->getArgument('user');
        $boardId = $input->getArgument('board');

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $userRepo = $this->dm->getRepository('PWUserBundle:User');
        $boardRepo = $this->dm->getRepository('PWBoardBundle:Board');
        $followRepo = $this->dm->getRepository('PWUserBundle:Follow');

        $board = $boardRepo->find($boardId);
        if (!$board) {
            throw new \Exception("Board $boardId doesn't exist");
        }

        $user = $userRepo->find($userId);
        if (!$user) {
            throw new \Exception("User $userId doesn't exist");
        }

        if ($board->getCreatedBy()->getId() != $user->getId()) {
            throw new \Exception("Board does not belong to user");
        }

        $conditions = array(
            'target.$id' => new \MongoId($userId),
        );
        $follows = $followRepo->createQueryBuilder()
            ->field('target')->references($user)
            ->getQuery()->execute();

        $count = count($follows);
        if (!$count) {
            $output->writeln("<comment>No followers found to process</comment>");
            return;
        }

        /* @var $followManager \PW\UserBundle\Model\FollowManager */
        $followManager = $this->getContainer()->get('pw_user.follow_manager');

        $count = 0;
        foreach ($follows as $follow) {
            if (!$follow->getIsActive()) {
                continue; //skip inactive follows
            }
            $output->writeln("<info>" . $follow->getFollower()->getName() . " now following board {$boardId}</info>");
            $boardFollow = $followManager->addFollower($follow->getFollower(), $board);
            $followManager->update($boardFollow);
            $count++;
        }

        $this->dm->flush(null, array('safe' => false, 'fsync' => false));

        $boardName = $board->getName();
        $userName = $user->getName();

        $output->writeln("<info>Added {$count} followers(s) to new board $boardName ($boardId) by $userName ($userId)</info>");
    }
}
