<?php

namespace PW\UserBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use PW\ApplicationBundle\Document\AbstractDocument;
use JMS\SerializerBundle\Annotation as API;
use PW\UserBundle\Document\User;

/**
 * @MongoDB\Document(collection="follows", repositoryClass="PW\UserBundle\Repository\FollowRepository")
 * @MongoDB\Indexes({
 *      @MongoDB\UniqueIndex(keys={"follower.$id"="asc", "target.$id"="asc"}, safe=true, background=true),
 *      @MongoDB\Index(keys={"target.$ref"="asc", "target.$id"="asc"}, safe=true, background=true)
 * })
 */
class Follow extends AbstractDocument
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * The User doing the following
     *
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User", cascade={"persist"})
     */
    protected $follower;

    /**
     * The User/Board being followed
     *
     * @MongoDB\ReferenceOne(cascade={"persist"})
     */
    protected $target;

    /**
     * The owner of the target (e.g. Board creator)
     *
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User", cascade={"persist"})
     */
    protected $user;

    /**
     * @Gedmo\Timestampable(on="create")
     * @MongoDB\Date
     */
    protected $created;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $createdBy;

    /**
     * @MongoDB\Date
     */
    protected $deleted;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $deletedBy;

    /**
     * @var bool
     * @MongoDB\Boolean
     */
    protected $isActive = true;

    /**
     * @var bool
     * @MongoDB\Boolean
     */
    protected $isFriend = false;

    /**
     * @var bool
     * @MongoDB\NotSaved
     */
    protected $noEmit = false;


    /**
     * @var bool
     * @MongoDB\Boolean
     */
    protected $isCeleb;

    /**
     * @param mixed $target
     */
    public function setFollowing($target)
    {
        return $this->setTarget($target);
    }

    /**
     * @param mixed $target
     */
    public function setTarget($target)
    {
        $this->target = $target;

        if ($target instanceOf User) {
            $this->setUser($target);
        } else {
            if ($target->getCreatedBy()) {
                $this->setUser($target->getCreatedBy());
            }
        }
    }

    /**
     * @param PW\UserBundle\Document\User $user
     */
    protected function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param PW\UserBundle\Document\User $deletedBy
     */
    public function setDeletedBy(User $deletedBy = null)
    {
        $this->deletedBy = $deletedBy;
    }

    /**
     * @return boolean $isFriend
     */
    public function getIsFriend()
    {
        if ($this->isFriend === null) {
            $this->isFriend = false;
        }
        return $this->isFriend;
    }

    /**
     * Set noEmit
     *
     * @param bool $noEmit
     */
    public function setNoEmit($noEmit)
    {
        $this->noEmit = (bool) $noEmit;
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
     * Get deletedBy
     *
     * @return PW\UserBundle\Document\User $deletedBy
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }

    /**
     * Set follower
     *
     * @param PW\UserBundle\Document\User $follower
     */
    public function setFollower(\PW\UserBundle\Document\User $follower)
    {
        $this->follower = $follower;
    }

    /**
     * Get follower
     *
     * @return PW\UserBundle\Document\User $follower
     */
    public function getFollower()
    {
        return $this->follower;
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
     * Set isFriend
     *
     * @param boolean $isFriend
     */
    public function setIsFriend($isFriend)
    {
        $this->isFriend = $isFriend;
    }

    /**
     * Get target
     *
     * @return mixed $target
     */
    public function getTarget()
    {
        return $this->target;
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
     * Get noEmit
     *
     * @return bool $noEmit
     */
    public function getNoEmit()
    {
        return $this->noEmit;
    }
    /**
     * @var date $modified
     */
    protected $modified;


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

    public function isCeleb()
    {
        return $this->isCeleb;
    }

    public function setIsCeleb($value)
    {
        $this->isCeleb = (Boolean)$value;
    }

    /**
     * Get isCeleb
     *
     * @return boolean $isCeleb
     */
    public function getIsCeleb()
    {
        return $this->isCeleb;
    }
}
