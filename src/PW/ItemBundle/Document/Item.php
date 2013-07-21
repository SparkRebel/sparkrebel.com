<?php

namespace PW\ItemBundle\Document;

use PW\ApplicationBundle\Document\AbstractDocument,
    Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    Gedmo\Mapping\Annotation as Gedmo;
    
/*
Index explanation:
for stream:refresh onsale:
    keys={"created"="desc", "isOnSale"="asc", "createdBy.$id"="asc"}
*/

/**
 * @MongoDB\Document(collection="items", repositoryClass="PW\ItemBundle\Repository\ItemRepository")
 * @MongoDB\Indexes({
 *      @MongoDB\Index(keys={"feedId"="asc"}, background=true),
 *      @MongoDB\Index(keys={"slug"="asc"}, background=true),
 *      @MongoDB\Index(keys={"created"="desc", "isOnSale"="asc", "createdBy.$id"="asc"}, background=true),
 *      @MongoDB\Index(keys={"isOnSale"="asc", "merchantUser.$id"="asc", "created"="desc"}, background=true)
 * })
 */
class Item extends AbstractDocument
{
    /**
     * @MongoDB\Id(strategy="INCREMENT")
     */
    protected $id;

    /**
     * @var String
     * @MongoDB\String
     */
    protected $merchantName;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     * @MongoDB\AlsoLoad("merchant")
     */
    protected $merchantUser;

    /**
     * @var String
     * @MongoDB\String
     */
    protected $brandName;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     * @MongoDB\AlsoLoad("brand")
     */
    protected $brandUser;

    /**
     * @var \PW\PostBundle\Document\Post
     * @MongoDB\ReferenceOne(targetDocument="PW\PostBundle\Document\Post")
     */
    protected $rootPost;

    /**
     * @MongoDB\ReferenceMany(targetDocument="PW\CategoryBundle\Document\Category")
     */
    protected $categories;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $feedId;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $name;

    /**
     * @var string
     * @Gedmo\Slug(fields={"name"})
     * @MongoDB\String
     */
    protected $slug;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $description;

    /**
     * @var string
     * @MongoDB\ReferenceOne(targetDocument="PW\AssetBundle\Document\Asset")
     */
    protected $imagePrimary;

    /**
     * @var array
     * @MongoDB\ReferenceMany(targetDocument="PW\AssetBundle\Document\Asset")
     */
    protected $images;

    /**
     * @MongoDB\String
     */
    protected $link;

    /**
     * @var float
     * @MongoDB\Float
     */
    protected $price = 0;

    /**
     * @var float
     * @MongoDB\Float
     */
    protected $pricePrevious = 0;

    /**
     * @var bool
     * @MongoDB\Boolean
     */
    protected $isActive = false;

    /**
     * @var bool
     * @MongoDB\Boolean
     */
    protected $isDiscontinued = false;

    /**
     * @var bool
     * @MongoDB\Boolean
     */
    protected $isOnSale = false;

    /**
     * @var array
     * @MongoDB\Collection
     */
    protected $tags;

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
     * @Gedmo\Timestampable(on="update")
     * @MongoDB\Date
     */
    protected $modified;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $modifiedBy;

    /**
     * @MongoDB\PreUpdate
     */
    public function preUpdate()
    {
        if ($this->getPrice() < $this->getPricePrevious()) {
            $this->setIsOnSale(true);
        }
    }

    public function __construct()
    {
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Add categories
     *
     * @param PW\CategoryBundle\Document\Category $categories
     */
    public function addCategories(\PW\CategoryBundle\Document\Category $categories)
    {
        $existing = $this->getCategories();
        foreach ($existing as $comparison) {
            if ($comparison->getId() === $categories->getId()) {
                return;
            }
        }
        $this->categories[] = $categories;
    }

    /**
     * Replace categories
     *
     * @param Array $categories
     */
    public function replaceCategories(Array $categories)
    {
        $this->categories = $categories;
    }


    //
    // Doctrine Generation Below
    //

    /**
     * Set merchantName
     *
     * @param string $merchantName
     */
    public function setMerchantName($merchantName)
    {
        $this->merchantName = $merchantName;
    }

    /**
     * Get merchantName
     *
     * @return string $merchantName
     */
    public function getMerchantName()
    {
        return $this->merchantName;
    }

    /**
     * Set merchantUser
     *
     * @param PW\UserBundle\Document\User $merchantUser
     */
    public function setMerchantUser(\PW\UserBundle\Document\User $merchantUser)
    {
        $this->merchantUser = $merchantUser;
    }

    /**
     * Get merchantUser
     *
     * @return PW\UserBundle\Document\User $merchantUser
     */
    public function getMerchantUser()
    {
        return $this->merchantUser;
    }

    /**
     * Set brandName
     *
     * @param string $brandName
     */
    public function setBrandName($brandName)
    {
        $this->brandName = $brandName;
    }

    /**
     * Get brandName
     *
     * @return string $brandName
     */
    public function getBrandName()
    {
        return $this->brandName;
    }

    /**
     * Set brandUser
     *
     * @param PW\UserBundle\Document\User $brandUser
     */
    public function setBrandUser(\PW\UserBundle\Document\User $brandUser)
    {
        $this->brandUser = $brandUser;
    }

    /**
     * Get brandUser
     *
     * @return PW\UserBundle\Document\User $brandUser
     */
    public function getBrandUser()
    {
        return $this->brandUser;
    }

    /**
     * Get categories
     *
     * @return Doctrine\Common\Collections\Collection $categories
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set feedId
     *
     * @param string $feedId
     */
    public function setFeedId($feedId)
    {
        $this->feedId = $feedId;
    }

    /**
     * Get feedId
     *
     * @return string $feedId
     */
    public function getFeedId()
    {
        return $this->feedId;
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
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get slug
     *
     * @return string $slug
     */
    public function getSlug()
    {
        return $this->slug;
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
     * Set imagePrimary
     *
     * @param PW\AssetBundle\Document\Asset $imagePrimary
     */
    public function setImagePrimary(\PW\AssetBundle\Document\Asset $imagePrimary)
    {
        $this->imagePrimary = $imagePrimary;
    }

    /**
     * Get imagePrimary
     *
     * @return PW\AssetBundle\Document\Asset $imagePrimary
     */
    public function getImagePrimary()
    {
        return $this->imagePrimary;
    }

    /**
     * Add images
     *
     * @param PW\AssetBundle\Document\Asset $images
     */
    public function addImages(\PW\AssetBundle\Document\Asset $images)
    {
        $this->images[] = $images;
    }

    /**
     * Get images
     *
     * @return Doctrine\Common\Collections\Collection $images
     */
    public function getImages()
    {
        return $this->images;
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
     * Set price
     *
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * Get price
     *
     * @return float $price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set pricePrevious
     *
     * @param float $pricePrevious
     */
    public function setPricePrevious($pricePrevious)
    {
        $this->pricePrevious = $pricePrevious;
    }

    /**
     * Get pricePrevious
     *
     * @return float $pricePrevious
     */
    public function getPricePrevious()
    {
        return $this->pricePrevious;
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
     * Set isDiscontinued
     *
     * @param boolean $isDiscontinued
     */
    public function setIsDiscontinued($isDiscontinued)
    {
        $this->isDiscontinued = $isDiscontinued;
    }

    /**
     * Get isDiscontinued
     *
     * @return boolean $isDiscontinued
     */
    public function getIsDiscontinued()
    {
        return $this->isDiscontinued;
    }

    /**
     * Set isOnSale
     *
     * @param boolean $isOnSale
     */
    public function setIsOnSale($isOnSale)
    {
        $this->isOnSale = $isOnSale;
    }

    /**
     * Get isOnSale
     *
     * @return boolean $isOnSale
     */
    public function getIsOnSale()
    {
        return $this->isOnSale;
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
     * Set rootPost
     *
     * @param PW\PostBundle\Document\Post $rootPost
     */
    public function setRootPost(\PW\PostBundle\Document\Post $rootPost)
    {
        $this->rootPost = $rootPost;
        $this->isActive = true;
    }

    /**
     * Get rootPost
     *
     * @return PW\PostBundle\Document\Post $rootPost
     */
    public function getRootPost()
    {
        return $this->rootPost;
    }
}
