<?php

namespace PW\ActivityBundle\Command;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    PW\ActivityBundle\Document\Notification;

/**
 * NotifyUserAllCommand
 */
class NotifyUserAllCommand extends NotifyUserCommand
{
    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('notify:user:all')
            ->setDescription('Recreate all notifications for a given user')
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
            ->field('user')->references($user)
            ->field('target.$ref')->equals('users')
            ->getQuery()->execute();

        foreach ($follows as $follow) {
            $this->process('user.follow', $follow->getId());

            try {
                $this->dm->flush(null, array('safe' => false, 'fsync' => false));
            } catch (\MongoCursorException $e) {
                $this->output->writeln("\t<error>Duplicate, activity already exists</error>");
            }
        }

        $follows = $this->dm->createQueryBuilder('PWUserBundle:Follow')
            ->field('user')->references($user)
            ->field('target.$ref')->equals('boards')
            ->getQuery()->execute();

        foreach ($follows as $follow) {
            $this->process('board.follow', $follow->getId());

            try {
                $this->dm->flush(null, array('safe' => false, 'fsync' => false));
            } catch (\MongoCursorException $e) {
                $this->output->writeln("\t<error>Duplicate, activity already exists</error>");
            }
        }

        $posts = $this->dm->createQueryBuilder('PWPostBundle:Post')
            ->field('createdBy')->references($user)
            ->field('repostCount')->gt(0)
            ->getQuery()->execute();

        foreach ($posts as $post) {
            $qb = $this->dm->createQueryBuilder('PWPostBundle:Post');

            $reposts = $this->dm->createQueryBuilder('PWPostBundle:Post')
                ->field('parent')->references($post)
                ->getQuery()->execute();

            foreach ($reposts as $repost) {
                $this->process('post.repost', $repost->getId());

                try {
                    $this->dm->flush(null, array('safe' => false, 'fsync' => false));
                } catch (\MongoCursorException $e) {
                    $this->output->writeln("\t<error>Duplicate, activity already exists</error>");
                }
            }
        }
    }
}
