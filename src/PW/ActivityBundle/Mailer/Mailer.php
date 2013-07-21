<?php

namespace PW\ActivityBundle\Mailer;

use PW\ApplicationBundle\Mailer\Mailer as BaseMailer;
use PW\ActivityBundle\Document\NotificationEmail;

class Mailer extends BaseMailer
{
    /**
     * @param \PW\ActivityBundle\Document\NotificationEmail $email
     * @return int
     * @throws \Exception
     */
    public function sendNotificationsEmailMessage(NotificationEmail $email)
    {
        if (!$email->getNotifications()->count()) {
            throw new \Exception('No notifications to send');
        }

        $user  = $email->getUser();
        if (!$user->getIsActive()) {
            // inactive user -> dont send
            return true;
        }
        $types = $user->getSettings()->getEmail()->getNotificationTypes();

        // First 5 only...
        $firstNotifications = array();
        foreach ($email->getNotifications() as $notification) {
            $firstNotifications[] = $notification;
            if (count($firstNotifications) >= 5) {
                break;
            }
        }

        // Prepare
        $template = $this->parameters['template']['notifications'];
        $context  = array(
            'email'         => $email,
            'user'          => $user,
            'notifications' => $firstNotifications,
            'types'         => $types,
        );
        $from = $this->parameters['from_email']['notifications'];

        $to = $user->getEmail();
        if (empty($to)) {
            throw new \Exception('Email address is empty');
        }

        $name = $user->getName();
        if (!empty($name)) {
            $to = array($to => $name);
        }

        // Update last send date
        $user->getSettings()->getEmail()->setNotificationLastSend(new \DateTime());
        $this->userManager->update($user);

        return $this->sendMessage($template, $context, $from, $to, 'notification-email');
    }
}