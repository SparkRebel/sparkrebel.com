<?php

namespace PW\OutfitBundle\Document;

use PW\ApplicationBundle\Document\AbstractDocument,
    Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    Gedmo\Mapping\Annotation as Gedmo;

/**
 * Outfit
 *
 * @MongoDB\Document(collection="outfits", repositoryClass="PW\OutfitBundle\Repository\OutfitRepository")
 */
class Outfit extends AbstractDocument
{
    /**
     * @MongoDB\Id(strategy="INCREMENT")
     */
    protected $id;

    /**
     * @MongoDB\EmbedMany(targetDocument="OutfitAsset")
     */
    protected $assets;

    /**
     * contributors
     *
     * @MongoDB\ReferenceMany(targetDocument="PW\UserBundle\Document\User")
     * @var mixed
     * @access protected
     */
    protected $contributors;

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
     * @MongoDB\Date
     */
    protected $deleted;

    /**
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $deletedBy;

    /**
     * @MongoDB\String
     */
    protected $description;

    /**
     * imageMap
     *
     * @MongoDB\EmbedOne(targetDocument="OutfitImageMap")
     * @var mixed
     * @access protected
     */
    protected $imageMap;

    /**
     * @MongoDB\Boolean
     */
    protected $isActive;

    /**
     * @MongoDB\Boolean
     */
    protected $isTweaked;

    /**
     * @MongoDB\EmbedMany(targetDocument="OutfitItem")
     */
    protected $items;

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
     * @MongoDB\String
     */
    protected $name;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Outfit")
     */
    protected $parent;

    /**
     * @MongoDB\Collection
     */
    protected $tags;

    public function __construct()
    {
        $this->assets = new \Doctrine\Common\Collections\ArrayCollection();
        $this->contributors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct();
    }

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
     * Add assets
     *
     * @param PW\OutfitBundle\Document\OutfitAsset $assets
     */
    public function addAssets(\PW\OutfitBundle\Document\OutfitAsset $assets)
    {
        $this->assets[] = $assets;
    }

    /**
     * Get assets
     *
     * @return Doctrine\Common\Collections\Collection $assets
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * Add contributors
     *
     * @param PW\UserBundle\Document\User $contributors
     */
    public function addContributors(\PW\UserBundle\Document\User $contributors)
    {
        $this->contributors[] = $contributors;
    }

    /**
     * Get contributors
     *
     * @return Doctrine\Common\Collections\Collection $contributors
     */
    public function getContributors()
    {
        return $this->contributors;
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
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set imageMap
     *
     * @param PW\OutfitBundle\Document\OutfitImageMap $imageMap
     */
    public function setImageMap(\PW\OutfitBundle\Document\OutfitImageMap $imageMap)
    {
        $this->imageMap = $imageMap;
    }

    /**
     * Get imageMap
     *
     * @return PW\OutfitBundle\Document\OutfitImageMap $imageMap
     */
    public function getImageMap()
    {
        return $this->imageMap;
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
     * Set isTweaked
     *
     * @param boolean $isTweaked
     */
    public function setIsTweaked($isTweaked)
    {
        $this->isTweaked = $isTweaked;
    }

    /**
     * Get isTweaked
     *
     * @return boolean $isTweaked
     */
    public function getIsTweaked()
    {
        return $this->isTweaked;
    }

    /**
     * Add items
     *
     * @param PW\OutfitBundle\Document\OutfitItem $items
     */
    public function addItems(\PW\OutfitBundle\Document\OutfitItem $items)
    {
        $this->items[] = $items;
    }

    /**
     * Get items
     *
     * @return Doctrine\Common\Collections\Collection $items
     */
    public function getItems()
    {
        return $this->items;
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
     * Set parent
     *
     * @param PW\OutfitBundle\Document\Outfit $parent
     */
    public function setParent(\PW\OutfitBundle\Document\Outfit $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return PW\OutfitBundle\Document\Outfit $parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set tags
     *
     * @param collection $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * Get tags
     *
     * @return collection $tags
     */
    public function getTags()
    {
        return $this->tags;
    }
}
