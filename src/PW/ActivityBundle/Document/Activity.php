<?php

namespace PW\ActivityBundle\Document;

use PW\ApplicationBundle\Document\AbstractDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @MongoDB\Document(collection="activities", repositoryClass="PW\ActivityBundle\Repository\ActivityRepository")
 * @MongoDB\Indexes({
 *      @MongoDB\UniqueIndex(keys={"user.$id"="asc", "target.$id"="asc", "type"="asc", "created"="desc"}, safe=true, dropDups=true, background=true),
 *      @MongoDB\Index(keys={"user.$id"="asc", "created"="desc"}, background=true),
 *      @MongoDB\Index(keys={"target.$id"="asc", "target.$ref"="asc"}, safe=true, background=true)
 * })
 */
class Activity extends AbstractDocument
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @Gedmo\Timestampable(on="create")
     * @MongoDB\Date
     */
    protected $created;

    /**
     * @Gedmo\Timestampable(on="update")
     * @MongoDB\Date
     */
    protected $modified;

    /**
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $createdBy;

    /**
     * @MongoDB\Hash
     */
    protected $data;

    /**
     * @MongoDB\String
     */
    protected $html;

    /**
     * @var boolean $isActive
     */
    protected $isActive;

    /**
     * @MongoDB\ReferenceOne
     */
    protected $target;

    /**
     * @MongoDB\String
     */
    protected $text;

    /**
     * @MongoDB\String
     */
    protected $type;

    /**
     * @MongoDB\String
     */
    protected $url;

    /**
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $user;

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

        if (!$this->text) {
            $this->setText($this->getText());
        }
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
        if (!$this->text) {
            return str_replace('&#039;', '\'', trim(preg_replace('@\s+@', ' ', html_entity_decode(strip_tags($this->getHtml())))));
        }
        return $this->text;
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
