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
    PW\InviteBundle\Document\Code;

/**
 * @MongoDB\Document(collection="invite_requests", repositoryClass="PW\InviteBundle\Repository\RequestRepository")
 * @MongoDB\Indexes({
 *      @MongoDB\UniqueIndex(keys={"email"="asc"}, background=true)
 * })
 * @MongoDBUnique(fields={"email"}, message="We have already received an invite request for this e-mail address.")
 * @ExclusionPolicy("none")
 */
class Request extends AbstractDocument
{
    /**
     * @var \MongoId
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @var string
     * @MongoDB\String
     * @Assert\NotBlank(message="Invite Email cannot be left blank.")
     * @Assert\Email()
     * @InviteAssert\EmailNotRegistered()
     */
    protected $email;

    /**
     * @var \DateTime
     * @MongoDB\Date
     */
    protected $requestedCodeAt;

    /**
     * @var \DateTime
     * @MongoDB\Date
     */
    protected $assignedCodeAt;

    /**
     * @var PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User", cascade={"persist"})
     */
    protected $assignedCodeBy;

    /**
     * @var \PW\InviteBundle\Document\Code
     * @MongoDB\ReferenceOne(targetDocument="PW\InviteBundle\Document\Code", inversedBy="assignedRequests", cascade={"persist"})
     */
    protected $code;

    /**
     * @var \DateTime
     * @MongoDB\Date
     */
    protected $redeemedCodeAt;

    /**
     * @var PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User", inversedBy="inviteRequest", cascade={"persist"})
     */
    protected $user;

    /**
     * @var \MongoDate
     * @MongoDB\Date
     * @Gedmo\Timestampable(on="create")
     */
    protected $created;

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
        $this->requestedCodeAt = new \DateTime();

        parent::__construct();
    }

    /**
     * Assigns an Invite Code to this Request
     *
     * @param Code $code
     * @param User $assignedBy
     */
    public function assignCode(Code $code, \PW\UserBundle\Document\User $assignedBy = null)
    {
        if ($code->getUsesLeft() !== null) {
            if ($code->getUsesLeft() <= 0) {
                throw new Exception(sprintf("Invite Code '%' has been used too many times.", $code->getValue()));
            }
        }

        $this->setCode($code);
        $this->setAssignedCodeAt(new \DateTime());
        if ($assignedBy !== null) {
            $this->setAssignedCodeBy($assignedBy);
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
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set requestedCodeAt
     *
     * @param date $requestedCodeAt
     */
    public function setRequestedCodeAt($requestedCodeAt)
    {
        $this->requestedCodeAt = $requestedCodeAt;
    }

    /**
     * Get requestedCodeAt
     *
     * @return date $requestedCodeAt
     */
    public function getRequestedCodeAt()
    {
        return $this->requestedCodeAt;
    }

    /**
     * Set assignedCodeAt
     *
     * @param date $assignedCodeAt
     */
    public function setAssignedCodeAt($assignedCodeAt)
    {
        $this->assignedCodeAt = $assignedCodeAt;
    }

    /**
     * Get assignedCodeAt
     *
     * @return date $assignedCodeAt
     */
    public function getAssignedCodeAt()
    {
        return $this->assignedCodeAt;
    }

    /**
     * Set assignedCodeBy
     *
     * @param PW\UserBundle\Document\User $assignedCodeBy
     */
    public function setAssignedCodeBy(\PW\UserBundle\Document\User $assignedCodeBy)
    {
        $this->assignedCodeBy = $assignedCodeBy;
    }

    /**
     * Get assignedCodeBy
     *
     * @return PW\UserBundle\Document\User $assignedCodeBy
     */
    public function getAssignedCodeBy()
    {
        return $this->assignedCodeBy;
    }

    /**
     * Set code
     *
     * @param PW\InviteBundle\Document\Code $code
     */
    public function setCode(\PW\InviteBundle\Document\Code $code)
    {
        $this->code = $code;
    }

    /**
     * Get code
     *
     * @return PW\InviteBundle\Document\Code $code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set redeemedCodeAt
     *
     * @param date $redeemedCodeAt
     */
    public function setRedeemedCodeAt($redeemedCodeAt)
    {
        $this->redeemedCodeAt = $redeemedCodeAt;
    }

    /**
     * Get redeemedCodeAt
     *
     * @return date $redeemedCodeAt
     */
    public function getRedeemedCodeAt()
    {
        return $this->redeemedCodeAt;
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
