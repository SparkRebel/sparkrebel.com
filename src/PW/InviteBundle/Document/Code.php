<?php

namespace PW\InviteBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    Gedmo\Mapping\Annotation as Gedmo,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique,
    JMS\SerializerBundle\Annotation\ExclusionPolicy,
    JMS\SerializerBundle\Annotation\Exclude,
    PW\ApplicationBundle\Document\AbstractDocument,
    PW\InviteBundle\Validator as InviteAssert,
    PW\UserBundle\Document\User;

/**
 * @MongoDB\Document(collection="invite_codes", repositoryClass="PW\InviteBundle\Repository\CodeRepository")
 * @MongoDB\Indexes({
 *      @MongoDB\UniqueIndex(keys={"value"="asc"}, background=true)
 * })
 * @MongoDBUnique(fields={"value"}, groups={"create"})
 * @ExclusionPolicy("none")
 */
class Code extends AbstractDocument
{
    /**
     * @var \MongoId
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @var string
     * @MongoDB\String
     * @InviteAssert\CodeIsValid(groups={"redeem"})
     */
    protected $value;

    /**
     * @var int
     * @MongoDB\Int
     */
    protected $maxUses;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $comment;

    /**
     * @var int
     * @MongoDB\Int
     */
    protected $usesLeft;

    /**
     * @var int
     * @MongoDB\Int
     */
    protected $usedCount;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceMany(targetDocument="PW\UserBundle\Document\User", cascade={"persist"})
     */
    protected $usedBy;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User", inversedBy="assignedInviteCode", cascade={"persist"}, simple=true)
     * @InviteAssert\UserNotAssignedCode(groups={"create"})
     */
    protected $assignedUser;

    /**
     * @MongoDB\ReferenceMany(targetDocument="PW\InviteBundle\Document\Request", mappedBy="code", cascade={"persist"})
     */
    protected $assignedRequests;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $type;

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
     */
    protected $deleted;

    /**
     * @var PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $deletedBy;

    /**
     * @var int
     * @MongoDB\NotSaved
     */
    protected $codeLength;

    public function __construct()
    {
        $this->assignedRequests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->usedCount  = 0;
        $this->codeLength = 6;

        parent::__construct();
    }

    /**
     * @return string
     */
    public function generateCode($length = null)
    {
        $characters = '23456789abcdefghkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        $string     = null;
        if ($length === null) {
            $length = $this->codeLength;
        }
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        return $string;
    }

    /**
     * Redeems an Invite Code for a User
     *
     * @param User $user
     */
    public function redeemCode(User $user)
    {
        $this->incUsedCount();
        $this->addUsedBy($user);
        $user->setUsedInviteCode($this);
        return $user;
    }

    public function incUsedCount()
    {
        ++$this->usedCount;
        if ($this->getUsesLeft()) {
            $this->decUsesLeft();
        }
    }

    /**
     * Get usesLeft
     *
     * @return int $usesLeft
     */
    public function getUsesLeft()
    {
        if ($this->usesLeft === null && $this->getMaxUses()) {
            $this->usesLeft = $this->getMaxUses();
        }
        return $this->usesLeft;
    }

    protected function decUsesLeft()
    {
        --$this->usesLeft;
        if ($this->usesLeft < 0) {
            $this->usesLeft = 0;
        }
    }


    /**
     * getAdminData
     *
     */
    public function getAdminData()
    {
        $return = array();
        $return['code'] = $this->getValue();
        if ($this->getAssignedUser()) {
            $return['user'] = $this->getAssignedUser()->getName();
        } elseif ($this->getCreatedBy()) {
            $return['user'] = $this->getCreatedBy()->getName();
        }

        return $return;
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
     * Set value
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get value
     *
     * @return string $value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set maxUses
     *
     * @param int $maxUses
     */
    public function setMaxUses($maxUses)
    {
        $this->maxUses = $maxUses;
    }

    /**
     * Get maxUses
     *
     * @return int $maxUses
     */
    public function getMaxUses()
    {
        return $this->maxUses;
    }

    /**
     * Set usesLeft
     *
     * @param int $usesLeft
     */
    public function setUsesLeft($usesLeft)
    {
        $this->usesLeft = $usesLeft;
    }

    /**
     * Set usedCount
     *
     * @param increment $usedCount
     */
    public function setUsedCount($usedCount)
    {
        $this->usedCount = $usedCount;
    }

    /**
     * Get usedCount
     *
     * @return increment $usedCount
     */
    public function getUsedCount()
    {
        return $this->usedCount;
    }

    /**
     * Set assignedUser
     *
     * @param PW\UserBundle\Document\User $assignedUser
     */
    public function setAssignedUser(\PW\UserBundle\Document\User $assignedUser)
    {
        $this->assignedUser = $assignedUser;
    }

    /**
     * Get assignedUser
     *
     * @return PW\UserBundle\Document\User $assignedUser
     */
    public function getAssignedUser()
    {
        return $this->assignedUser;
    }

    /**
     * Add assignedRequests
     *
     * @param PW\InviteBundle\Document\Request $assignedRequests
     */
    public function addAssignedRequests(\PW\InviteBundle\Document\Request $assignedRequests)
    {
        $this->assignedRequests[] = $assignedRequests;
    }

    /**
     * Get assignedRequests
     *
     * @return Doctrine\Common\Collections\Collection $assignedRequests
     */
    public function getAssignedRequests()
    {
        return $this->assignedRequests;
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
     * Add usedBy
     *
     * @param PW\UserBundle\Document\User $usedBy
     */
    public function addUsedBy(\PW\UserBundle\Document\User $usedBy)
    {
        $this->usedBy[] = $usedBy;
    }

    /**
     * Get usedBy
     *
     * @return Doctrine\Common\Collections\Collection $usedBy
     */
    public function getUsedBy()
    {
        return $this->usedBy;
    }

    /**
     * Set comment
     *
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get comment
     *
     * @return string $comment
     */
    public function getComment()
    {
        return $this->comment;
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
