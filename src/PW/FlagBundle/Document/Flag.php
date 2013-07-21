<?php

namespace PW\FlagBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    Gedmo\Mapping\Annotation as Gedmo,
    Symfony\Component\Validator\Constraints as Assert,
    JMS\SerializerBundle\Annotation\ExclusionPolicy,
    JMS\SerializerBundle\Annotation\Exclude,
    PW\ApplicationBundle\Document\AbstractDocument,
    PW\UserBundle\Document\User;

/**
 * Flag
 *
 * @MongoDB\Document(collection="flags", repositoryClass="PW\FlagBundle\Repository\FlagRepository")
 * @ExclusionPolicy("none")
 */
class Flag extends AbstractDocument
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String
     */
    protected $client;

    /**
     * @MongoDB\Int
     */
    protected $ip;

    /**
     * MongoDB\String
     */
    protected $userAgent;

    /**
     * @MongoDB\Hash
     */
    protected $details;

    /**
     * @MongoDB\String
     */
    protected $reason;

    /**
     * @MongoDB\ReferenceOne(cascade={"persist"})
     */
    protected $target;

    /**
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User", cascade={"persist"})
     */
    protected $targetUser;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $url;

    /**
     * @var string
     * @MongoDB\String
     * @Assert\Choice(choices={"inappropriate", "comment", "copyright", "other"}, message="Invalid flag type.")
     */
    protected $type;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $status;

    /**
     * @var \DateTime
     * @MongoDB\Date
     */
    protected $statusUpdatedAt;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $statusUpdatedBy;

    /**
     * @var \MongoDate
     * @MongoDB\Date
     * @Gedmo\Timestampable(on="create")
     */
    protected $created;

    /**
     * @var PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $createdBy;

    /**
     * @var \MongoDate
     * @MongoDB\Date
     * @Gedmo\Timestampable(on="update")
     */
    protected $modified;

    /**
     * @var PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $modifiedBy;

    /**
     * @var \MongoDate
     * @MongoDB\Date
     */
    protected $deleted;

    /**
     * @var PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $deletedBy;

    public function __construct()
    {
        $this->status = 'pending';
        parent::__construct();
    }

    /**
     * @param User $approvedBy
     */
    public function approve(User $approvedBy = null)
    {
        $this->setStatus('approved');
        $this->setStatusUpdatedAt(new \DateTime());
        if ($approvedBy) {
            $this->setStatusUpdatedBy($approvedBy);
        }

        if ($this->getCreatedBy()) {
            $this->getCreatedBy()->getFlagSummary()->incTotalByApproved();
        }

        if ($this->getTargetUser()) {
            $this->getTargetUser()->getFlagSummary()->incTotalAgainstApproved();
        }
    }

    /**
     * @param User $rejectedBy
     */
    public function reject(User $rejectedBy = null)
    {
        $this->setStatus('rejected');
        $this->setStatusUpdatedAt(new \DateTime());
        if ($rejectedBy) {
            $this->setStatusUpdatedBy($rejectedBy);
        }

        if ($this->getCreatedBy()) {
            $this->getCreatedBy()->getFlagSummary()->incTotalByRejected();
        }

        if ($this->getTargetUser()) {
            $this->getTargetUser()->getFlagSummary()->incTotalAgainstRejected();
        }
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
     * Set client
     *
     * @param string $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * Get client
     *
     * @return string $client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set ip
     *
     * @param int $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * Get ip
     *
     * @return int $ip
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set details
     *
     * @param hash $details
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }

    /**
     * Get details
     *
     * @return hash $details
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set reason
     *
     * @param string $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    /**
     * Get reason
     *
     * @return string $reason
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set target
     *
     * @param $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * Get target
     *
     * @return $target
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set targetUser
     *
     * @param PW\UserBundle\Document\User $targetUser
     */
    public function setTargetUser(\PW\UserBundle\Document\User $targetUser)
    {
        $this->targetUser = $targetUser;
    }

    /**
     * Get targetUser
     *
     * @return PW\UserBundle\Document\User $targetUser
     */
    public function getTargetUser()
    {
        return $this->targetUser;
    }

    /**
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set statusUpdatedAt
     *
     * @param date $statusUpdatedAt
     */
    public function setStatusUpdatedAt($statusUpdatedAt)
    {
        $this->statusUpdatedAt = $statusUpdatedAt;
    }

    /**
     * Get statusUpdatedAt
     *
     * @return date $statusUpdatedAt
     */
    public function getStatusUpdatedAt()
    {
        return $this->statusUpdatedAt;
    }

    /**
     * Set statusUpdatedBy
     *
     * @param PW\UserBundle\Document\User $statusUpdatedBy
     */
    public function setStatusUpdatedBy(\PW\UserBundle\Document\User $statusUpdatedBy)
    {
        $this->statusUpdatedBy = $statusUpdatedBy;
    }

    /**
     * Get statusUpdatedBy
     *
     * @return PW\UserBundle\Document\User $statusUpdatedBy
     */
    public function getStatusUpdatedBy()
    {
        return $this->statusUpdatedBy;
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
     * Set modifiedBy
     *
     * @param PW\UserBundle\Document\User $modifiedBy
     */
    public function setModifiedBy(\PW\UserBundle\Document\User $modifiedBy)
    {
        $this->modifiedBy = $modifiedBy;
    }

    /**
     * Get modifiedBy
     *
     * @return PW\UserBundle\Document\User $modifiedBy
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }

    /**
     * Set deleted
     *
     * @param date $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
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
     * Set deletedBy
     *
     * @param PW\UserBundle\Document\User $deletedBy
     */
    public function setDeletedBy(\PW\UserBundle\Document\User $deletedBy = null)
    {
        $this->deletedBy = $deletedBy;
    }

    /**
     * Get deletedBy
     *
     * @return PW\UserBundle\Document\User $deletedBy
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }

    /**
     * Set createdBy
     *
     * @param PW\UserBundle\Document\User $createdBy
     */
    public function setCreatedBy(\PW\UserBundle\Document\User $createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * Get createdBy
     *
     * @return PW\UserBundle\Document\User $createdBy
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get url
     *
     * @return string $url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * @var boolean $isActive
     */
    protected $isActive;


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
}
