<?php

namespace PW\ApplicationBundle\Model;

use PW\UserBundle\Document\User\Settings\Email as EmailSettings;

/**
 * @method \PW\ApplicationBundle\Repository\EmailRepository getRepository()
 * @method \PW\ApplicationBundle\Document\Email find() find(string $id)
 * @method \PW\ApplicationBundle\Document\Email create() create(array $data)
 * @method void delete() delete(\PW\ApplicationBundle\Document\Email $email, \PW\UserBundle\Document\User $deletedBy, bool $safe, bool $andFlush)
 */
class EmailManager extends AbstractManager
{
    /**
     * @var array
     */
    protected $flushOptions = array(
        'safe'  => false,
        'fsync' => false,
    );

    /**
     * @param string $frequency
     * @param \DateTime $lastSendDate
     * @return boolean|\DateTime
     */
    public function getNextSendDate($frequency, $lastSendDate = null)
    {
        switch ($frequency) {
            case EmailSettings::FREQUENCY_ASAP:
                $nextSendDate = new \DateTime('+5 minutes');
                break;
            case EmailSettings::FREQUENCY_DAILY:
                if ($lastSendDate) {
                    $nextSendDate = $lastSendDate->modify('+1 day');
                } else {
                    $nextSendDate = new \DateTime('tomorrow');
                }
                $nextSendDate->setTime(0, 0, 0);
                break;
            case EmailSettings::FREQUENCY_WEEKLY:
                if ($lastSendDate) {
                    $nextSendDate = $lastSendDate->modify('+1 week');
                } else {
                    $nextSendDate = new \DateTime('next Sunday');
                }
                $nextSendDate->setTime(0, 0, 0);
                break;
            case EmailSettings::FREQUENCY_MONTHLY:
                if ($lastSendDate) {
                    $nextSendDate = $lastSendDate->modify('+1 month');
                } else {
                    $nextSendDate = new \DateTime('first day of next month');
                }
                $nextSendDate->setTime(0, 0, 0);
                break;
            default:
                return false;
        }

        return $nextSendDate;
    }
}
