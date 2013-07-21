<?php

namespace PW\UserBundle\Document\User\Settings;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gedmo\Mapping\Annotation as Gedmo;
use PW\ApplicationBundle\Document\AbstractDocument;
use PW\ActivityBundle\Document\Notification;

/**
 * @MongoDB\EmbeddedDocument
 */
class Email extends AbstractDocument
{
    const FREQUENCY_NEVER   = 'never';
    const FREQUENCY_ASAP    = 'asap';
    const FREQUENCY_DAILY   = 'daily';
    const FREQUENCY_WEEKLY  = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';

    /**
     * @var string
     * @MongoDB\String
     */
    protected $notificationFrequency;

    /**
     * @var array
     */
    protected $defaultNotificationTypes = array(
        'board_follow'   => true,
        'comment_create' => true,
        'comment_tag'    => true,
        'comment_reply'  => true,
        'post_repost'    => true,
        'user_follow'    => true,
        'newsletter'     => true
    );

    /**
     * @var array
     * @MongoDB\Hash
     */
    protected $notificationTypes;

    /**
     * @var \DateTime
     * @MongoDB\Date
     */
    protected $notificationLastSend;

    /**
     * @var bool
     * @MongoDB\NotSaved
     */
    protected $isActive;

    /**
     * @var \DateTime
     * @MongoDB\NotSaved
     */
    protected $deleted;

    /**
     * @return string
     */
    public function getAdminValue()
    {
        return null;
    }

    public function __construct(array $data = array())
    {
        $this->notificationFrequency = self::FREQUENCY_ASAP;
        $this->notificationTypes = $this->defaultNotificationTypes;

        parent::__construct($data);
    }

    /**
     * @param string|Notification $type
     * @return boolean
     */
    public function isNotificationEnabled($type)
    {
        $frequency = $this->getNotificationFrequency();
        if (empty($frequency) || $frequency === self::FREQUENCY_NEVER) {
            return false;
        }

        if (is_object($type) && $type instanceOf Notification) {
            $type = str_replace('.', '_', $type->getType());
        }

        $types = $this->getNotificationTypes();
        return (bool) $types[$type];
    }

    //
    // Doctrine Generation Below
    //

    /**
     * Set notificationFrequency
     *
     * @param string $notificationFrequency
     */
    public function setNotificationFrequency($notificationFrequency)
    {
        $this->notificationFrequency = $notificationFrequency;
    }

    /**
     * Get notificationFrequency
     *
     * @return string $notificationFrequency
     */
    public function getNotificationFrequency()
    {
        return $this->notificationFrequency;
    }

    /**
     * Set notificationTypes
     *
     * @param hash $notificationTypes
     */
    public function setNotificationTypes($notificationTypes)
    {
        $this->notificationTypes = $notificationTypes;
    }

    /**
     * Get notificationTypes
     *
     * @return hash $notificationTypes
     */
    public function getNotificationTypes()
    {
        /**
         * this is useful when adding new default notification types
         */
        foreach($this->defaultNotificationTypes as $defaultNotificationType => $value)
        {
            if(!isset($this->notificationTypes[$defaultNotificationType]))
            {
                $this->notificationTypes[$defaultNotificationType] = $value;
            }
        }

        return $this->notificationTypes;
    }

    /**
     * Set isActive
     *
     * @param string $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * Get isActive
     *
     * @return string $isActive
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set deleted
     *
     * @param string $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * Get deleted
     *
     * @return string $deleted
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set notificationLastSend
     *
     * @param date $notificationLastSend
     */
    public function setNotificationLastSend($notificationLastSend)
    {
        $this->notificationLastSend = $notificationLastSend;
    }

    /**
     * Get notificationLastSend
     *
     * @return date $notificationLastSend
     */
    public function getNotificationLastSend()
    {
        return $this->notificationLastSend;
    }
    /**
     * @var date $created
     */
    protected $created;

    /**
     * @var date $modified
     */
    protected $modified;


    /**
     * Set created
     *
     * @param date $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return date $created
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modified
     *
     * @param date $modified
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
    }

    /**
     * Get modified
     *
     * @return date $modified
     */
    public function getModified()
    {
        return $this->modified;
    }
}
