<?php

namespace PW\ActivityBundle\Model;

use PW\ApplicationBundle\Model\AbstractManager;
use PW\ApplicationBundle\Model\EmailManager;
use PW\UserBundle\Document\User;
use PW\UserBundle\Document\User\Settings\Email as EmailSettings;
use PW\ActivityBundle\Document\Notification;
use PW\ActivityBundle\Document\NotificationEmail;

/**
 * @method \PW\ActivityBundle\Repository\NotificationRepository getRepository()
 * @method \PW\ActivityBundle\Document\Notification find() find(string $id)
 * @method \PW\ActivityBundle\Document\Notification create() create(array $data)
 * @method void delete() delete(\PW\ActivityBundle\Document\Notification $notification, \PW\UserBundle\Document\User $deletedBy, bool $safe, bool $andFlush)
 */
class NotificationManager extends AbstractManager
{
    /**
     * @var array
     */
    protected $flushOptions = array(
        'safe'  => false,
        'fsync' => false,
    );

    /**
     * @var \PW\ApplicationBundle\Model\EmailManager
     */
    protected $emailManager;

    /**
     * @param \PW\UserBundle\Document\User $user
     * @param string $notificationType
     * @return \PW\ActivityBundle\Document\NotificationEmail
     */
    public function addOrUpdateEmail(User $user, Notification $notification)
    {
        /* @var $emailSettings \PW\UserBundle\Document\User\Settings\Email */
        $emailSettings = $user->getSettings()->getEmail();
        $frequency     = $emailSettings->getNotificationFrequency();
        $lastSendDate  = $emailSettings->getNotificationLastSend();

        $email = $user->getEmail();
        if (empty($email)) {
            return false;
        }

        if (!$emailSettings->isNotificationEnabled($notification)) {
            return false;
        }

        /* @var $email \PW\ApplicationBundle\Document\Email */
        $email = $this->emailManager->getRepository()
            ->findByUserAndType($user, 'notifications')
            ->getQuery()->getSingleResult();

        if ($email) {
            if ($frequency === EmailSettings::FREQUENCY_ASAP) {
                $email->bumpScheduledDate();
            }
        } else {
            $email = new NotificationEmail();
            $email->setUser($user);
            if ($scheduledDate = $this->emailManager->getNextSendDate($frequency, $lastSendDate)) {
                $email->setOriginalScheduledDate($scheduledDate);
            }
        }

        $email->addNotifications($notification);
        $this->emailManager->save($email, array('validate' => false));

        return $email;
    }

    /**
     * @param \PW\ApplicationBundle\Model\EmailManager $emailManager
     */
    public function setEmailManager(EmailManager $emailManager)
    {
        $this->emailManager = $emailManager;
    }
}
