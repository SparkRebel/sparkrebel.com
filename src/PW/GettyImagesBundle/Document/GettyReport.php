<?php

namespace PW\GettyImagesBundle\Document;

use PW\ApplicationBundle\Document\AbstractDocument,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    Gedmo\Mapping\Annotation as Gedmo;

/**
 * GettyReport
 *
 * @MongoDB\Document(collection="getty_reports", repositoryClass="PW\GettyImagesBundle\Repository\GettyReportRepository")
 */
class GettyReport extends AbstractDocument
{
    /**
     * @MongoDB\Id
     */
    protected $id;
    
    /**
     * @var string
     * @MongoDB\String
     */
    protected $status; // possible statuses: new, generating, generated, creating_preview, ready_to_send, sending, sent
    
     /**
     * @var string
     * @MongoDB\String
     */
    protected $textStatus;   

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
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $possible_statuses = array('new', 'generating', 'generated', 'creating_preview', 'ready_to_send', 'sending', 'sent');
        if (in_array($status, $possible_statuses)) {
            return $this->status = $status;
        } else {
            return false;
        }
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
     * Set textStatus
     *
     * @param string $textStatus
     */
    public function setTextStatus($textStatus)
    {
        $this->textStatus = $textStatus;
    }

    /**
     * Get textStatus
     *
     * @return string $textStatus
     */
    public function getTextStatus()
    {
        return $this->textStatus;
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
     * Can sending of this report be initiated?
     *
     * @return bool
     */   
    public function canSend()
    {
        return $this->getStatus() == 'ready_to_send';
    }    
    
     /**
     * Returns path to preview file, or false if preview is not available yet
     *
     * @return mixed
     */   
    public function getPreviewFilePath()
    {
        if (in_array($this->getStatus(), array('ready_to_send', 'sending', 'sent'))) {
            return '/tmp/getty_report_preview_'.$this->getId().'.csv';
        }
        return false;
    }   
}
