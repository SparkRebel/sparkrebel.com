<?php

namespace PW\GettyImagesBundle\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    FOS\UserBundle\Model\User as BaseUser,
    JMS\SerializerBundle\Annotation as API,
    PW\UserBundle\Document\User;

use PW\ApplicationBundle\Document\AbstractDocument as Document;

/**
 * @MongoDB\Document("GettyUsage")
 * @MongoDB\Indexes({
 *      @MongoDB\Index(keys={"status"="asc", "gettyId"="asc"}, sparse=true, background=true),
 * })
 * @API\ExclusionPolicy("none")
 * @API\AccessType("public_method")
 */
class Usage extends Document
{
    /**  
     *  @MongoDB\Id(strategy="INCREMENT")
     *  @API\Accessor(getter="getId", setter="setId")
     */
    protected $id;

    /** @MongoDB\Int */
    protected $gettyId;

    /** @MongoDB\ReferenceOne(targetDocument="PW\AssetBundle\Document\Asset") */
    protected $asset;

    /** @MongoDB\Hash */
    protected $details;
    
    /** @MongoDB\Int */
    protected $quantity;

    /** @MongoDB\Date */
    protected $date;

    /** @MongoDB\String */
    protected $month;

    /** @MongoDB\Int */
    protected $sent = 0;

    /** @MongoDB\Int */
    protected $tobeSend = 0;

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
     * Get id
     *
     * @return custom_id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set gettyId
     *
     * @param int $gettyId
     * @return Usage
     */
    public function setGettyId($gettyId)
    {
        $this->gettyId = $gettyId;
        return $this;
    }

    /**
     * Get gettyId
     *
     * @return int $gettyId
     */
    public function getGettyId()
    {
        return $this->gettyId;
    }

    /**
     * Set asset
     *
     * @param PW\AssetBundle\Document\Asset $asset
     * @return Usage
     */
    public function setAsset(\PW\AssetBundle\Document\Asset $asset)
    {
        $this->asset = $asset;
        return $this;
    }

    /**
     * Get asset
     *
     * @return PW\AssetBundle\Document\Asset $asset
     */
    public function getAsset()
    {
        return $this->asset;
    }

    /**
     * Set quantity
     *
     * @param int $quantity
     * @return Usage
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * Get quantity
     *
     * @return int $quantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set date
     *
     * @param date $date
     * @return Usage
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return date $date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set sent
     *
     * @param int $sent
     * @return Usage
     */
    public function setSent($sent)
    {
        $this->sent = $sent;
        return $this;
    }

    /**
     * Get sent
     *
     * @return int $sent
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Usage
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
        return $this;
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
     * @return Usage
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
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
     * @return Usage
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
        return $this;
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
     * Set deleted
     *
     * @param date $deleted
     * @return Usage
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
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
     * Set month
     *
     * @param string $month
     * @return Usage
     */
    public function setMonth($month)
    {
        $this->month = $month;
        return $this;
    }

    /**
     * Get month
     *
     * @return string $month
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Set tobeSend
     *
     * @param int $tobeSend
     * @return Usage
     */
    public function setToBeSent($tobeSend)
    {
        $this->tobeSend = $tobeSend;
        return $this;
    }

    /**
     * Get tobeSend
     *
     * @return int $tobeSend
     */
    public function getToBeSent()
    {
        return $this->tobeSend;
    }

    /**
     * Set details
     *
     * @param hash $details
     * @return Usage
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
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
     * Set tobeSend
     *
     * @param int $tobeSend
     * @return Usage
     */
    public function setTobeSend($tobeSend)
    {
        $this->tobeSend = $tobeSend;
        return $this;
    }

    /**
     * Get tobeSend
     *
     * @return int $tobeSend
     */
    public function getTobeSend()
    {
        return $this->tobeSend;
    }
}
