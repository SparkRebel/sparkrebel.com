<?php

namespace PW\ItemBundle\Document;

use PW\ApplicationBundle\Document\AbstractDocument,
    Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    Gedmo\Mapping\Annotation as Gedmo;

/**
 * Brand/Merchant whitelist
 *
 * Which brands are we going to process. If there's no record in this collection
 * Don't import the data
 *
 * @MongoDB\Document(collection="brand_merchant_whitelist")
 */
class Whitelist extends AbstractDocument
{
    /**
     * @MongoDB\Id(strategy="NONE")
     */
    protected $id;

    /**
     * @var bool
     * @MongoDB\Boolean
     */
    protected $isActive = true;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $type;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
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
}
