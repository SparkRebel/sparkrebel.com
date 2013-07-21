<?php

namespace PW\UserBundle\Command;

use PW\UserBundle\Document\Follow,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * FollowFbfriendsCommand
 *
 * Find all the facebook friends of a user - and make friends
 */
class FollowFbfriendsCommand extends ContainerAwareCommand
{
    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('follow:fbfriends')
            ->setDescription('Find all facebook friends for a user, and make friends')
            ->setDefinition(array(
                new InputArgument('user', InputArgument::REQUIRED, 'The user $id'),
            ));
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userId = $input->getArgument('user');
        $userManager = $this->getContainer()->get('pw_user.user_manager');

        /* @var \PW\UserBundle\Document\User */
        $user = $userManager->find($userId);

        if (!$user) {
            throw new \RuntimeException("User ID could not be found: {$userId}");
        }

        if (!$user->getFacebookId()) {
            $output->writeln("<comment>User does not have a Facebook Id: {$userId}</comment>");
            return;
        }

        $next = null;
        $facebookProvider = $this->getContainer()->get('pw.facebook.user');
        do {
            $result = $facebookProvider->getInstalledFriends($user, $next);
            $users  = $result['users'];
            $next   = $result['next'];

            $fbIds = $this->getInstalledFacebookIds($users);
            foreach ($fbIds as $fbId) {
                $friend = $facebookProvider->findUserByFacebookId($fbId);
                if ($friend) {
                    $output->writeln("<info>Making {$friend->getName()} friends with {$user->getName()}</info>");
                    $userManager->makeFriends($user, $friend);
                } else {
                    $output->writeln("<error>Couldn't find User with fbId: {$fbId}</error>");
                }
            }

        } while ($next !== false);
    }

    /**
     * @param array $friends
     * @return array
     */
    protected function getInstalledFacebookIds(array $friends = array())
    {
        if (isset($friends['data'])) {
            $friends = $friends['data'];
        }

        $installed = array();
        foreach ($friends as $friend) {
            if (isset($friend['id']) && isset($friend['installed'])) {
                $installed[] = $friend['id'];
            }
        }

        return $installed;
    }
}
