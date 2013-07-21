<?php

namespace PW\UserBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    Gedmo\Mapping\Annotation as Gedmo,
    Symfony\Component\Validator\Constraints as Assert,
    JMS\SerializerBundle\Annotation\ExclusionPolicy,
    JMS\SerializerBundle\Annotation\Exclude,
    PW\ApplicationBundle\Document\AbstractDocument,
    PW\UserBundle\Document\User,
    PW\UserBundle\Document\Brand;

/**
 * @MongoDB\Document(collection="partner_requests", repositoryClass="PW\UserBundle\Repository\PartnerRepository")
 * @MongoDB\Indexes({
 *      @MongoDB\UniqueIndex(keys={"requestedSlug"="asc"}, background=true, safe=false, sparse=true)
 * })
 * @ExclusionPolicy("none")
 */
class Partner extends AbstractDocument
{
    /**
     * @var \MongoId
     * @MongoDB\Id
     */
    protected $id;

    /**
     * Should default to something which gives /images/items/blank.png
     *
     * @MongoDB\ReferenceOne(targetDocument="PW\AssetBundle\Document\Asset")
     */
    protected $icon;

    /**
     * @var string
     * @MongoDB\String
     * @Assert\NotBlank(message="User Email cannot be left blank.")
     * @Assert\Email()
     */
    protected $email;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string
     * @MongoDB\String
     */
    protected $plainPassword;

    /**
     * @var string
     * @MongoDB\String
     * @Assert\NotBlank(message="User Name cannot be left blank.")
     */
    protected $name;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $link;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $phone;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $requestedSlug;

    /**
     * @var PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $user;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $status;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $statusReason;

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
    }

    /**
     * Gets the plain password.
     *
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Sets the plain password.
     *
     * @param string $password
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    /**
     * @param string $type
     * @return \PW\UserBundle\Document\User
     */
    public function approve($type)
    {
        switch ($type) {
            case 'user':
                $user = $this->convertToUser();
                break;
            case 'brand':
                $user = $this->convertToBrand();
                break;
            default:
                throw new Exception("'{$type}' is an invalid User type.");
                break;
        }
        $this->setStatus('approved');
        return $user;
    }

    /**
     * @param string $message
     */
    public function reject($message = null)
    {
        $this->setStatus('rejected');
        $this->setStatusReason($message);
    }

    /**
     * @return \PW\UserBundle\Document\Brand
     */
    public function convertToBrand()
    {
        $brand = new Brand();
        return $this->convertToUser($brand);
    }

    /**
     * @param User $user
     * @return \PW\UserBundle\Document\User
     */
    public function convertToUser(\PW\UserBundle\Document\User $user = null)
    {
        if (!($this->getUser() instanceOf User)) {
            if ($user === null) {
                $user = new User();
            }
            $user->setName($this->getName());
            $user->setEmail($this->getEmail());
            $user->setPlainPassword($this->getPlainPassword());
            $user->setEnabled(true);
        }
        $user->addRole('ROLE_PARTNER');
        return $user;
    }

    /**
     * @param \PW\UserBundle\Document\User $user
     * @return \PW\UserBundle\Document\Partner
     */
    public function setFromUser(\PW\UserBundle\Document\User $user)
    {
        $this->setName($user->getName());
        $this->setEmail($user->getEmail());
        $this->setUser($user);
        return $this;
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
     * Set icon
     *
     * @param PW\AssetBundle\Document\Asset $icon
     */
    public function setIcon(\PW\AssetBundle\Document\Asset $icon)
    {
        $this->icon = $icon;
    }

    /**
     * Get icon
     *
     * @return PW\AssetBundle\Document\Asset $icon
     */
    public function getIcon()
    {
        return $this->icon;
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set link
     *
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * Get link
     *
     * @return string $link
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set phone
     *
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * Get phone
     *
     * @return string $phone
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set requestedSlug
     *
     * @param string $requestedSlug
     */
    public function setRequestedSlug($requestedSlug)
    {
        $this->requestedSlug = $requestedSlug;
    }

    /**
     * Get requestedSlug
     *
     * @return string $requestedSlug
     */
    public function getRequestedSlug()
    {
        return $this->requestedSlug;
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
     * Set statusReason
     *
     * @param string $statusReason
     */
    public function setStatusReason($statusReason)
    {
        $this->statusReason = $statusReason;
    }

    /**
     * Get statusReason
     *
     * @return string $statusReason
     */
    public function getStatusReason()
    {
        return $this->statusReason;
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
