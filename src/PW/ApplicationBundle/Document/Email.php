<?php

namespace PW\ApplicationBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\SoftDelete\SoftDeleteable;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * An e-mail not quite ready to be sent that is being prepared.
 *
 * @MongoDB\Document(collection="emails", repositoryClass="PW\ApplicationBundle\Repository\EmailRepository")
 * @MongoDB\InheritanceType("SINGLE_COLLECTION")
 * @MongoDB\DiscriminatorField(fieldName="type")
 * @MongoDB\DiscriminatorMap({"standard"="Email", "notifications"="PW\ActivityBundle\Document\NotificationEmail"})
 * @MongoDB\Indexes({
 *      @MongoDB\UniqueIndex(keys={"isActive"="asc", "type"="asc", "user"="asc"}, safe=true, background=true)
 * })
 */
class Email extends AbstractDocument
{
    /**
     * @var \MongoId
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     * @MongoDB\Hash
     */
    protected $data;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User", cascade={"persist"}, simple=true)
     */
    protected $user;

    /**
     * @var \DateTime
     * @MongoDB\Date
     */
    protected $scheduledDate;

    /**
     * @var \DateTime
     * @MongoDB\Date
     */
    protected $originalScheduledDate;

    /**
     * @var \DateTime
     * @MongoDB\Date
     */
    protected $sentDate;

    /**
     * @var boolean
     * @MongoDB\NotSaved
     */
    protected $isActive = true;

    /**
     * @Gedmo\Timestampable(on="create")
     * @MongoDB\Date
     */
    protected $created;

    /**
     * @Gedmo\Timestampable(on="update")
     * @MongoDB\Date
     */
    protected $modified;

    /**
     * @var \MongoDate
     * @MongoDB\NotSaved
     */
    protected $deleted;

    /**
     * Set originalScheduledDate
     *
     * @param \DateTime $originalScheduledDate
     */
    public function setOriginalScheduledDate($originalScheduledDate)
    {
        $this->originalScheduledDate = $originalScheduledDate;
        $this->scheduledDate         = $originalScheduledDate;
    }

    /**
     * @param int $minutes
     */
    public function bumpScheduledDate($minutes = 5)
    {
        $scheduledDate   = $this->getScheduledDate();
        $scheduledDateTs = $scheduledDate->getTimestamp();
        $originalDate    = $this->getOriginalScheduledDate();
        $originalDateTs  = $originalDate->getTimestamp();
        $diffMinutes     = ($scheduledDateTs - $originalDateTs) / 60;
        if ($diffMinutes > 10) {
            // Subtract time to ensure we send ASAP
            $this->setScheduledDate(new \DateTime('-5 minutes'));
        } else {
            // Add X minutes to buffer for more notifications
            $this->setScheduledDate($scheduledDate->modify("+{$minutes} minutes"));
        }
    }

    /**
     * Set deleted
     *
     * @param date $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted  = $deleted;
        $this->isActive = false;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'email';
    }

    //
    // Doctrine Generation Below
    //

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
     * Set data
     *
     * @param hash $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Get data
     *
     * @return hash $data
     */
    public function getData()
    {
        return $this->data;
    }
}
