<?php

namespace PW\ActivityBundle\Command;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    PW\ActivityBundle\Document\Notification;

/**
 * NotifyFriendsAllCommand
 */
class NotifyFriendsAllCommand extends NotifyUserCommand
{
    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('notify:friends:all')
            ->setDescription('Recreate all notifications for a given user\'s friends')
            ->setDefinition(array(
                new InputArgument(
                    'userId',
                    InputArgument::REQUIRED,
                    'The user id to process, or "*" for all users'
                )
            ));
    }

    /**
     * Call process with the passed args
     *
     * Inherited by all the commands in this bundle
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');

        $id = $input->getArgument('userId');

        $qb = $this->dm->getRepository('PWUserBundle:User');
        $conditions = array();
        if ($id !== '*') {
            $conditions['_id'] = new \Mongoid($id);
        }

        $users = $this->dm->getRepository('PWUserBundle:User')->findBy($conditions);
        foreach ($users as $user) {
            $this->output->writeln("Processing " . $user->getName());
            $this->processUser($user);
        }
    }

    /**
     * processUser
     *
     * @param mixed $user instance
     */
    protected function processUser($user)
    {
        $follows = $this->dm->createQueryBuilder('PWUserBundle:Follow')
            ->count()
            ->field('user')->references($user)
            ->field('target.$ref')->equals('users')
            ->field('isFriend')->equals(true)
            ->getQuery()->execute();

        if (!$follows) {
            $this->output->writeln("\tNo friends");
            return;
        }

        $em = $this->getContainer()->get('pw.event');
        $em->setMode('foreground');

        $activities = $this->dm->createQueryBuilder('PWActivityBundle:Activity')
            ->field('user')->references($user)
            ->field('target.$ref')->equals('users')
            ->getQuery()->execute();

        if (!$activities->count()) {
            $this->output->writeln("\tNo activities to process");
            return;
        }

        foreach ($activities as $activity) {
            $event = $activity->getEvent();
            $this->output->writeln("\tNotifying $follows friends of $event");
            $em->requesteJob("notify:friends $event " . $activity->getTarget()->getId());
        }
    }
}
