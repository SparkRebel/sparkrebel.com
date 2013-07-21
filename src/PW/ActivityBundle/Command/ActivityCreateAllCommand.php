<?php

namespace PW\ActivityBundle\Command;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    PW\ActivityBundle\Document\Notification;

/**
 * ActivityCreateAllCommand
 */
class ActivityCreateAllCommand extends ActivityCreateCommand
{
    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('activity:create:all')
            ->setDescription('Create activity for a user\'s whole activity')
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
        $comments = $this->dm->createQueryBuilder('PWPostBundle:PostComment')
            ->field('createdBy')->references($user)
            ->getQuery()->execute();

        foreach ($comments as $comment) {
            if ($comment->getPost()) {
                $this->process('comment.create', $comment->getId());
            } else {
                $this->process('comment.reply', $comment->getId());
            }

            try {
                $this->dm->flush(null, array('safe' => false, 'fsync' => false));
            } catch (\MongoCursorException $e) {
                $this->output->writeln("\t<error>Duplicate, activity already exists</error>");
            }
        }

        $follows = $this->dm->createQueryBuilder('PWUserBundle:Follow')
            ->field('createdBy')->references($user)
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
            ->field('follower')->references($user)
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
            ->field('isActive')->equals(true)
            ->field('createdBy.$id')->equals(new \MongoId($user->getId()))
            ->getQuery()->execute();

        foreach ($posts as $post) {
            $this->process('post.create', $post->getId());

            try {
                $this->dm->flush(null, array('safe' => false, 'fsync' => false));
            } catch (\MongoCursorException $e) {
                $this->output->writeln("\t<error>Duplicate, activity already exists</error>");
            }
        }
    }
}
