<?php

namespace PW\ItemBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @MongoDB\Document(collection="feed_items")
 * @MongoDB\Indexes({
 *      @MongoDB\Index(keys={"fid"="asc"}, background=true)
 * })
 */
class FeedItem
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String
     */
    protected $action = 'created';

    /**
     * @MongoDB\String
     */
    protected $brand;

    /**
     * @MongoDB\Collection
     */
    protected $categories;

    /**
     * @MongoDB\Collection
     */
    protected $categories_with_meta;

    /**
     * @MongoDB\Hash
     */
    protected $colors;

    /**
     * @MongoDB\String
     */
    protected $description;

    /**
     * @MongoDB\String
     */
    protected $fid;

    /**
     * @MongoDB\String
     */
    protected $main_image;

    /**
     * @MongoDB\Collection
     */
    protected $images;

    /**
     * @MongoDB\Hash(name="image_refs")
     */
    protected $imagesRef;

    /**
     * @MongoDB\Boolean
     */
    protected $is_deleted;

    /**
     * @MongoDB\String
     */
    protected $link;

    /**
     * @MongoDB\String
     */
    protected $merchant;

    /**
     * @MongoDB\Date
     */
    protected $modified;

    /**
     * @MongoDB\String
     */
    protected $name;

    /**
     * @MongoDB\Boolean
     */
    protected $on_sale;

    /**
     * @MongoDB\Float
     */
    protected $price;

    /**
     * @MongoDB\Hash
     */
    protected $price_history;

    /**
     * @MongoDB\String
     */
    protected $status = 'pending';

    /**
     * @MongoDB\String
     */
    protected $source;


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
     * Set action
     *
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Get action
     *
     * @return string $action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set brand
     *
     * @param string $brand
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    /**
     * Get brand
     *
     * @return string $brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set categories
     *
     * @param collection $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * Get categories
     *
     * @return collection $categories
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set categories_with_meta
     *
     * @param collection $categoriesWithMeta
     */
    public function setCategoriesWithMeta($categoriesWithMeta)
    {
        $this->categories_with_meta = $categoriesWithMeta;
    }

    /**
     * Get categories_with_meta
     *
     * @return collection $categoriesWithMeta
     */
    public function getCategoriesWithMeta()
    {
        return $this->categories_with_meta;
    }

    /**
     * Set colors
     *
     * @param hash $colors
     */
    public function setColors($colors)
    {
        $this->colors = $colors;
    }

    /**
     * Get colors
     *
     * @return hash $colors
     */
    public function getColors()
    {
        return $this->colors;
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
     * Set fid
     *
     * @param string $fid
     */
    public function setFid($fid)
    {
        $this->fid = $fid;
    }

    /**
     * Get fid
     *
     * @return string $fid
     */
    public function getFid()
    {
        return $this->fid;
    }

    /**
     * Set main_image
     *
     * @param string $mainImage
     */
    public function setMainImage($mainImage)
    {
        $this->main_image = $mainImage;
    }

    /**
     * Get main_image
     *
     * @return string $mainImage
     */
    public function getMainImage()
    {
        return $this->main_image;
    }

    /**
     * Set images
     *
     * @param collection $images
     */
    public function setImages($images)
    {
        $this->images = $images;
    }

    /**
     * Get images
     *
     * @return collection $images
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Set imagesRef
     *
     * @param hash $imagesRef
     */
    public function setImagesRef($imagesRef)
    {
        $this->imagesRef = $imagesRef;
    }

    /**
     * Get imagesRef
     *
     * @return hash $imagesRef
     */
    public function getImagesRef()
    {
        return $this->imagesRef;
    }

    /**
     * Set is_deleted
     *
     * @param boolean $isDeleted
     */
    public function setIsDeleted($isDeleted)
    {
        $this->is_deleted = $isDeleted;
    }

    /**
     * Get is_deleted
     *
     * @return boolean $isDeleted
     */
    public function getIsDeleted()
    {
        return $this->is_deleted;
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
     * Set merchant
     *
     * @param string $merchant
     */
    public function setMerchant($merchant)
    {
        $this->merchant = $merchant;
    }

    /**
     * Get merchant
     *
     * @return string $merchant
     */
    public function getMerchant()
    {
        return $this->merchant;
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
     * Set on_sale
     *
     * @param boolean $onSale
     */
    public function setOnSale($onSale)
    {
        $this->on_sale = $onSale;
    }

    /**
     * Get on_sale
     *
     * @return boolean $onSale
     */
    public function getOnSale()
    {
        return $this->on_sale;
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
     * Set price_history
     *
     * @param hash $priceHistory
     */
    public function setPriceHistory($priceHistory)
    {
        $this->price_history = $priceHistory;
    }

    /**
     * Get price_history
     *
     * @return hash $priceHistory
     */
    public function getPriceHistory()
    {
        return $this->price_history;
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
     * Set source
     *
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * Get source
     *
     * @return string $source
     */
    public function getSource()
    {
        return $this->source;
    }
}
