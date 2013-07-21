<?php

namespace PW\ActivityBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use PW\ApplicationBundle\Document\Email as BaseEmail;

/**
 * @MongoDB\Document
 */
class NotificationEmail extends BaseEmail
{
    /**
     * @var array
     * @MongoDB\ReferenceMany(targetDocument="PW\ActivityBundle\Document\Notification", simple=true, cascade={"remove"})
     */
    protected $notifications;

    /**
     * @var array
     */
    protected $data = array('users' => array(), 'types' => array());

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->notifications = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Add notifications
     *
     * @param PW\ActivityBundle\Document\Notification $notifications
     */
    public function addNotifications(\PW\ActivityBundle\Document\Notification $notification)
    {
        $this->notifications[] = $notification;

        $type = $notification->getType();
        $data = $this->getData();
        if ($type && $data) {
            // Dots in field names are not allowed
            $found = false;
            foreach ($data['types'] as $i => $typeData) {
                if ($typeData['name'] == $type) {
                    $data['types'][$i]['count'] += 1; $found = true;
                    break;
                }
            }
            
            // Not found, add it...
            if (!$found) {
                $data['types'][] = array(
                    'name'  => $type,
                    'count' => 1,
                );
            }
            
            if ($type == 'user.follow' || $type == 'board.follow') {
                if ($name = $notification->getTarget()->getFollower()->getName()) {
                    if (!in_array($name, $data['users'])) {
                        $data['users'][] = $name;
                    }
                }
            } else {
                if ($name = $notification->getCreatedBy()->getName()) {
                    if (!in_array($name, $data['users'])) {
                        $data['users'][] = $name;
                    }
                }
            }

            $this->setData($data);
        }
    }

    /**
     * Set originalScheduledDate
     *
     * @param date $originalScheduledDate
     */
    public function setOriginalScheduledDate($originalScheduledDate)
    {
        parent::setOriginalScheduledDate($originalScheduledDate);
    }

    /**
     * Set deleted
     *
     * @param date $deleted
     */
    public function setDeleted($deleted)
    {
        parent::setDelete($delete);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'notifications';
    }
    
    //
    // Doctrine Generation Below
    //

    /**
     * @var $id
     */
    protected $id;

    /**
     * @var date $scheduledDate
     */
    protected $scheduledDate;

    /**
     * @var date $originalScheduledDate
     */
    protected $originalScheduledDate;

    /**
     * @var date $sentDate
     */
    protected $sentDate;

    /**
     * @var boolean $isActive
     */
    protected $isActive;

    /**
     * @var date $created
     */
    protected $created;

    /**
     * @var date $modified
     */
    protected $modified;

    /**
     * @var date $deleted
     */
    protected $deleted;

    /**
     * @var PW\UserBundle\Document\User
     */
    protected $user;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user
     *
     * @param PW\UserBundle\Document\User $user
     */
    public function setUser(\PW\UserBundle\Document\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return PW\UserBundle\Document\User $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set scheduledDate
     *
     * @param date $scheduledDate
     */
    public function setScheduledDate($scheduledDate)
    {
        $this->scheduledDate = $scheduledDate;
    }

    /**
     * Get scheduledDate
     *
     * @return date $scheduledDate
     */
    public function getScheduledDate()
    {
        return $this->scheduledDate;
    }

    /**
     * Get originalScheduledDate
     *
     * @return date $originalScheduledDate
     */
    public function getOriginalScheduledDate()
    {
        return $this->originalScheduledDate;
    }

    /**
     * Set sentDate
     *
     * @param date $sentDate
     */
    public function setSentDate($sentDate)
    {
        $this->sentDate = $sentDate;
    }

    /**
     * Get sentDate
     *
     * @return date $sentDate
     */
    public function getSentDate()
    {
        return $this->sentDate;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * Get isActive
     *
     * @return boolean $isActive
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

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

    /**
     * Get deleted
     *
     * @return date $deleted
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Get notifications
     *
     * @return Doctrine\Common\Collections\Collection $notifications
     */
    public function getNotifications()
    {
        return $this->notifications;
    }
}
