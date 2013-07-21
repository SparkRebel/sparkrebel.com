<?php

namespace PW\ActivityBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="notifications", repositoryClass="PW\ActivityBundle\Repository\NotificationRepository")
 * @MongoDB\Indexes({
 *      @MongoDB\Index(keys={"created"="desc", "user.$id"="asc", "category"="asc", "target.$id"="asc" }, background=true),
 *      @MongoDB\UniqueIndex(keys={"user.$id"="asc", "target.$id"="asc", "type"="asc", "category"="asc", "created"="desc"}, safe=true, dropDups=true, background=true),
 *      @MongoDB\Index(keys={"target.$id"="asc", "target.$ref"="asc"}, safe=true, background=true)
 * })
 */
class Notification extends Activity
{
    /**
     * @var bool
     * @MongoDB\Boolean
     */
    protected $isNew = true;

    /**
     * @MongoDB\String
     */
    protected $category = 'user';

    //
    // Doctrine Generation Below
    //

    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @var date $created
     */
    protected $created;

    /**
     * @var hash $data
     */
    protected $data;

    /**
     * @var string $html
     */
    protected $html;

    /**
     * @var string $text
     */
    protected $text;

    /**
     * @var string $type
     */
    protected $type;

    /**
     * @var string $url
     */
    protected $url;

    /**
     * @var PW\UserBundle\Document\User
     */
    protected $createdBy;

    /**
     * @var object
     */
    protected $target;

    /**
     * @var PW\UserBundle\Document\User
     */
    protected $user;

    /**
     * Set isNew
     *
     * @param boolean $isNew
     */
    public function setIsNew($isNew)
    {
        $this->isNew = $isNew;
    }

    /**
     * Get isNew
     *
     * @return boolean $isNew
     */
    public function getIsNew()
    {
        return $this->isNew;
    }

    /**
     * Set category
     *
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * Get category
     *
     * @return string $category
     */
    public function getCategory()
    {
        return $this->category;
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
     * Set data
     *
     * @param hash $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Get data
     *
     * @return hash $data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set html
     *
     * @param string $html
     */
    public function setHtml($html)
    {
        $this->html = $html;
    }

    /**
     * Get html
     *
     * @return string $html
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Set target
     *
     * @param $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * Get target
     *
     * @return $target
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set text
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Get text
     *
     * @return string $text
     */
    public function getText()
    {
        return parent::getText();
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

    /**
     * Set url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get url
     *
     * @return string $url
     */
    public function getUrl()
    {
        return $this->url;
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
     * @var date $deleted
     */
    protected $deleted;


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
}
