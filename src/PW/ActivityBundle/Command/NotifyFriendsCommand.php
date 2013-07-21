<?php

namespace PW\ActivityBundle\Command;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    PW\ActivityBundle\Document\Notification;

/**
 * NotifyFriendsCommand
 */
class NotifyFriendsCommand extends NotifyUserCommand
{
    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('notify:friends')
            ->setDescription('Notify friends of a user\'s activity')
            ->setDefinition(array(
                new InputArgument(
                    'id',
                    InputArgument::REQUIRED,
                    'The activity id'
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

        $id = $input->getArgument('id');

        $activityRepo = $this->dm->getRepository('PWActivityBundle:Activity');
        $activity = $activityRepo->find($id);

        if (!$activity) {
            return 0;
        }

        $this->process($activity->getType(), $id);

        $this->dm->flush(null, array('safe' => false, 'fsync' => false));
    }

    /**
     * process
     *
     * Create Notifications for friends
     *
     * @param string $event  event name
     * @param string $id     id for the main entity in the activity
     * @param string $userId the user id to process, optional and only used for comment tagging
     */
    protected function process($event, $id, $userId = null)
    {
        $activityRepo = $this->dm->getRepository('PWActivityBundle:Activity');
        $activity = $activityRepo->find($id);
        $data['user'] = $activity->getUser();

        $friends = $this->dm->getRepository('PWUserBundle:Follow')->createQueryBuilder()
            ->field('follower')->references($data['user'])
            ->field('isFriend')->equals(true)
            ->field('target.$ref')->equals('users')
            ->getQuery()->execute();

        $count = count($friends);
        if (!$count) {
            $name = $data['user']->getName();
            $this->output->writeln("<comment>{$event} triggered, no friends of {$name} to notify</comment>");
            return;
        }

        $data['type'] = $activity->getType();
        $data['target'] = $activity->getTarget();
        $data['created'] = $activity->getCreated();
        $data['createdBy'] = $activity->getUser();
        $data['data'] = $activity->getData();
        $data['category'] = 'friend';


        switch ($activity->getType()) {
            case 'board.follow':
            case 'user.follow':
                $alreadyNotified = $activity->getTarget()->getUser();
                break;
            case 'comment.create':
            case 'comment.tag':
                $alreadyNotified = $activity->getTarget()->getPost()->getCreatedBy();
                break;
            default:
                $alreadyNotified = $activity->getTarget()->getCreatedBy();
        }
        if ($alreadyNotified) {
            $alreadyNotified = $alreadyNotified->getId();
        }

        foreach ($friends as $follow) {
            $data['user'] = $follow->getUser();
            $notification = new Notification($data);
            if (empty($data['html'])) {
                $data['html'] = $this->render($data['type'], $notification);
                $notification->setHtml($data['html']);
            }
            if ($data['user']->getId() === $alreadyNotified) {
                $notification->setIsNew(false);
            }
            $this->dm->persist($notification);
        }

        $this->dm->flush(null, array('safe' => false, 'fsync' => false));

        if (!$userId) {
            $userId = $data['user']->getId();
        }
        $this->output->writeln("<info>{$event} notification created for {$count} friends of {$userId} (targetid {$id})</info>");
    }
}
