<?php

namespace PW\JobBundle\Document;

use PW\ApplicationBundle\Document\AbstractDocument,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    Gedmo\Mapping\Annotation as Gedmo;

/**
 * Job
 *
 * @MongoDB\Document(collection="jobs", repositoryClass="PW\JobBundle\Repository\JobRepository")
 */
class Job extends AbstractDocument
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @var string
     * @MongoDB\String
     * @Assert\NotBlank(message="Cmd cannot be left blank.")
     */
    protected $cmd;
    
    /**
     * @var string
     * @MongoDB\String
     * @Assert\NotBlank(message="Keywords cannot be left blank.")
     */
    protected $keywords;
    
    /**
     * @var \PW\BoardBundle\Document\Board
     * @MongoDB\ReferenceOne(targetDocument="PW\BoardBundle\Document\Board", cascade={"persist"})
     * @Assert\Valid
     */
    protected $board;
    
    /**
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $user;
    
    /**
     * @MongoDB\Date
     */
    protected $startDate;
    
    /**
     * @MongoDB\Date
     */
    protected $endDate;
    
    /**
     * @MongoDB\Boolean
     */
    protected $isActive;
    
    /**
     * @Gedmo\Timestampable(on="create")
     * @MongoDB\Date
     */
    protected $created;
    
    /**
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $createdBy;
    
    /**
     * @Gedmo\Timestampable(on="update")
     * @MongoDB\Date
     */
    protected $modified;

    /**
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $modifiedBy;

    /**
     * @MongoDB\Date
     */
    protected $deleted;

    /**
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $deletedBy;
    
    
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
     * Set cmd
     *
     * @param string $cmd
     */
    public function setCmd($cmd)
    {
        $this->cmd = $cmd;
    }

    /**
     * Get cmd
     *
     * @return string $cmd
     */
    public function getCmd()
    {
        return $this->cmd;
    }
    
    /**
     * Set keywords
     *
     * @param string $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * Get keywords
     *
     * @return string $keywords
     */
    public function getKeywords()
    {
        return $this->keywords;
    }
    
    /**
     * Set board
     *
     * @param PW\BoardBundle\Document\Board $board
     */
    public function setBoard($board)
    {
        $this->board = $board;
    }

    /**
     * Get board
     *
     * @return PW\BoardBundle\Document\Board $board
     */
    public function getBoard()
    {
        return $this->board;
    }
    
    /**
     * Set user
     *
     * @param $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return $user
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * Set startDate
     *
     * @param date $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * Get startDate
     *
     * @return date $startDate
     */
    public function getStartDate()
    {
        return $this->startDate;
    }
    
    /**
     * Set endDate
     *
     * @param date $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * Get endDate
     *
     * @return date $endDate
     */
    public function getEndDate()
    {
        return $this->endDate;
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
     * Things to do before inserting
     *
     * If no start time is set - set to right now.
     * If no end time is set - set to 1 day duration
     *
     * @MongoDB\PrePersist
     */
    public function prePersist()
    {
        if (!$this->startDate) {
            $this->setStartDate(time());
        }
        if (!$this->endDate) {
            $this->setEndDate(time() + 60 * 60 * 24 * 1);
        }
    }
    
    
     /**
     * Is this Job running now, in future, expired, or is inactive?
     *
     * @return string
     */   
    public function getRunningStatus()
    {
        if (!$this->getIsActive()) return 'inactive';
        if ($this->getStartDate()->getTimestamp() <= time() && $this->getEndDate()->getTimestamp() >= time()) return 'active';
        if ($this->getEndDate()->getTimestamp() < time()) return 'expired';   
        return 'future';
    }
}
