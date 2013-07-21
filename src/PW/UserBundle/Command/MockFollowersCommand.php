<?php

namespace PW\UserBundle\Command;

use PW\ApplicationBundle\Command\AbstractMockCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MockFollowersCommand extends AbstractMockCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('mock:followers')
             ->setDescription('Generate random followers for a User');
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $followManager \PW\UserBundle\Model\FollowManager */
        $followerManager = $this->getFollowerManager();

        $count = 0;
        $user  = $this->getUser($input, $output);
        $total = $input->getOption('total');
        $users = $this->getUserManager()->getRepository()
            ->createQueryBuilder()
            ->eagerCursor(false)
            ->field('isActive')->equals(true)
            ->getQuery()->execute();

        $output->writeln('');
        foreach ($users as $follower /* @var $follower \PW\UserBundle\Document\User */) {
            if ($follower->getId() == $user->getId()) {
                $output->writeln('<comment>Skipping self...</comment>');
                continue;
            }

            if ($followerManager->isFollowing($follower, $user)) {
                $output->writeln('<comment>Skipping User that is already followed...</comment>');
                continue;
            } else {
                $followerManager->addFollower($follower, $user);
                $count++;
            }

            if ($count === $total) {
                $output->writeln('');
                $output->writeln("Added <info>{$total}</info> new Followers to User <info>'{$user->getId()}'</info>...");
                break;
            }
        }

        if ($count !== $total) {
            $output->writeln('');
            $output->writeln("<error>Unable to generate {$total} followers. Generated: {$count}</error>");
        }
    }

    /**
     * @return \PW\UserBundle\Model\FollowManager
     */
    protected function getFollowerManager()
    {
        return $this->getContainer()->get('pw_user.follow_manager');
    }
}
