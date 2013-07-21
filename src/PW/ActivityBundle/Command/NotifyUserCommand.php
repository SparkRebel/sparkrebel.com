<?php

namespace PW\ActivityBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PW\ActivityBundle\Document\Notification;

class NotifyUserCommand extends ActivityCreateCommand
{
    protected function configure()
    {
        $this
            ->setName('notify:user')
            ->setDescription('Notify a user of some activity')
            ->setDefinition(array(
                new InputArgument('event', InputArgument::REQUIRED, 'The event name'),
                new InputArgument('id', InputArgument::REQUIRED, 'The main id for whatever the notification relates to'),
                new InputArgument('userId', InputArgument::OPTIONAL, 'The user id of the user to notify. Only used where the id is ambiguous (comment tagging)'),
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
        parent::execute($input, $output);
    }

    /**
     * Create Notifications for one user
     *
     * @param string $event  event name
     * @param string $id     id for the main entity in the notification
     * @param string $userId the user id to process, optional and only used for comment tagging
     */
    protected function process($event, $id, $userId = null)
    {
        $method = str_replace('.', '', $event);

        $data = $this->$method($id, $userId);
        if (!$data) {
            $this->output->writeln("<comment>Notification *not* created for {$event} ({$id})</comment>");
            return;
        }

        $data['type'] = $event;

        if (!$data['target']) {
            $this->output->writeln("<comment>Notification *not* created for. Target not avilable.</comment>");
            return;
        }

        if (empty($data['created'])) {
            $data['created'] = $data['target']->getCreated();
        }
        if (empty($data['createdBy'])) {
            $data['createdBy'] = $data['target']->getCreatedBy();
        }
        if (empty($data['user'])) {
            $data['user'] = $data['createdBy'];
        }
        
        if (!$data['user'] || !$data['user']->getIsActive()) {
            $this->output->writeln("<comment>Notification *not* created for {$event} ({$id}) - user not active or doesnt exists</comment>");
            return;
        }
        
        if ($data['createdBy'] && $data['user']->getId() === $data['createdBy']->getId()) {
            $this->output->writeln("<comment>Notification *not* created for {$event} ({$id}) - same User who created it</comment>");
            return;
        }

        if ($data['user']->hasDisabledNotifications()) {
            $this->output->writeln("<comment>Notification *not* created for {$event} ({$id}) - user has disabled notifications</comment>");
            return;
        }

        if (!empty($data['data'])) {
            foreach ($data['data'] as &$value) {
                if (is_callable(array($value, 'getId'))) {
                    $value = $value->getId();
                }
            }
        }

        $notification = new Notification($data);
        $html = $this->render($event, $notification);
        $notification->setHtml($html);

        $this->dm->persist($notification);
        $this->dm->flush(null, array('safe' => false, 'fsync' => false));

        if (!$userId) {
            $userId = $data['user']->getId();
        }
        $this->output->writeln("<info>Notification created for {$event} (id: {$id} - userId: {$userId})</info>");

        $notificationManager = $this->getContainer()->get('pw_activity.notification_manager');
        if ($email = $notificationManager->addOrUpdateEmail($data['user'], $notification)) {
            if ($email->getOriginalScheduledDate() == $email->getScheduledDate()) {
                $this->output->writeln('Email scheduled to be sent at: <info>' . $email->getScheduledDate()->format('Y-m-d H:i:s') . '</info>');
            } else {
                $this->output->writeln('Email schedule updated to be sent at: <info>' . $email->getScheduledDate()->format('Y-m-d H:i:s') . '</info>');
            }
        } else {
            $this->output->writeln('<comment>Email was *not* scheduled</comment>');
        }
    }

    /**
     * boardFollow
     *
     * The parent function will return false if the user is also following the user
     *
     * @param string $id target id
     *
     * @return array
     */
    protected function boardFollow($id)
    {
        $return = parent::boardFollow($id);

        if (!$return) {
            return false;
        }

        $return['user'] = $return['target']->getUser();

        return $return;
    }

    /**
     * commentCreate
     *
     * @param string $id target id
     *
     * @return array
     */
    protected function commentCreate($id)
    {
        $return = parent::commentCreate($id);

        if (!$return) {
            return false;
        }

        $return['user'] = $return['target']->getPost()->getCreatedBy();
        return $return;
    }

    /**
     * commentReply
     *
     * @param string $id target id
     *
     * @return array
     */
    protected function commentReply($id)
    {
        $return = parent::commentReply($id);

        if (!$return) {
            return false;
        }

        $return['user'] = $return['replyTo']->getCreatedBy();
        return $return;
    }

    /**
     * commentTag
     *
     * @param string $id     target id
     * @param string $userId The id of the user that has been tagged
     *
     * @return array
     */
    protected function commentTag($id, $userId)
    {
        $return['target'] = $this->dm->getRepository('PWPostBundle:PostComment')->find($id);
        $return['user'] = $this->dm->getRepository('PWUserBundle:User')->find($userId);
        return $return;
    }

    /**
     * postRepost
     *
     * @param string $id target id
     *
     * @return array
     */
    protected function postRepost($id)
    {
        $return['target'] = $this->dm->getRepository('PWPostBundle:Post')->find($id);
        $return['user'] = $return['target']->getParent()->getCreatedBy();
        
        return $return;
    }

    /**
     * userFollow
     *
     * @param string $id target id
     *
     * @return array
     */
    protected function userFollow($id)
    {
        $return['target'] = $this->dm->getRepository('PWUserBundle:Follow')->find($id);
        if (!$return['target']) {
            return $return;
        }
        $return['user'] = $return['target']->getUser();
        return $return;
    }

    /**
     * render
     *
     * @param string $template     the event name, most likely
     * @param object $notification passed to the template to render
     * @param string $format       html or (in the future, for email alerts) text
     *
     * @return string
     */
    protected function render($template, $notification, $format = 'html')
    {
        $view = "PWActivityBundle:Notification:partials/$template.$format.twig";
        $parameters = compact('notification');
        return $this->getContainer()->get('templating')->render($view, $parameters);
    }
}
