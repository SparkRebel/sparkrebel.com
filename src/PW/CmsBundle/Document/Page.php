<?php

namespace PW\CmsBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    Gedmo\Mapping\Annotation as Gedmo,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique,
    JMS\SerializerBundle\Annotation\ExclusionPolicy,
    JMS\SerializerBundle\Annotation\Exclude,
    PW\ApplicationBundle\Document\AbstractDocument,
    PW\CmsBundle\Validator as CmsAssert,
    PW\UserBundle\Document\User,
    Symfony\Component\Routing\Route;

/**
 * @MongoDB\Document(collection="cms_pages", repositoryClass="PW\CmsBundle\Repository\PageRepository")
 * @MongoDB\Indexes({
 *      @MongoDB\UniqueIndex(keys={"slug"="asc"}, background=true),
 *      @MongoDB\UniqueIndex(keys={"url"="asc"}, background=true),
 *      @MongoDB\Index(keys={"section"="asc"}, background=true),
 *      @MongoDB\Index(keys={"subsection"="asc"}, background=true)
 * })
 * @MongoDBUnique(fields={"url"})
 * @ExclusionPolicy("none")
 */
class Page extends AbstractDocument
{
    /**
     * @var \MongoId
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $url;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $section;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $subsection;

    /**
     * @var int
     * @MongoDB\Int
     */
    protected $subsectionOrder;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $title;

    /**
     * @var string
     * @Gedmo\Slug(fields={"title"}, separator="_")
     * @MongoDB\String
     */
    protected $slug;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $content;

    /**
     * @var bool
     * @MongoDB\Boolean
     */
    protected $isActive;

    /**
     * @var \MongoDate
     * @MongoDB\Date
     * @Gedmo\Timestampable(on="create")
     */
    protected $created;

    /**
     * @var PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $createdBy;

    /**
     * @var \MongoDate
     * @MongoDB\Date
     */
    protected $deleted;

    /**
     * @var PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $deletedBy;

    /**
     * @param string $controller
     * @return \Symfony\Component\Routing\Route
     */
    public function getRoute($controller)
    {
        return new Route($this->getUrl(), array(
            '_controller' => $controller,
            'id' => $this->getId()
        ));
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
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return string $content
     */
    public function getContent()
    {
        return $this->content;
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
     * Set section
     *
     * @param string $section
     */
    public function setSection($section)
    {
        $this->section = $section;
    }

    /**
     * Get section
     *
     * @return string $section
     */
    public function getsection()
    {
        return $this->section;
    }

    /**
     * Set subsection
     *
     * @param string $subsection
     */
    public function setSubsection($subsection)
    {
        $this->subsection = $subsection;
    }

    /**
     * Get subsection
     *
     * @return string $subsection
     */
    public function getSubsection()
    {
        return $this->subsection;
    }

    /**
     * Set subsectionOrder
     *
     * @param int $subsectionOrder
     */
    public function setSubsectionOrder($subsectionOrder)
    {
        $this->subsectionOrder = $subsectionOrder;
    }

    /**
     * Get subsectionOrder
     *
     * @return int $subsectionOrder
     */
    public function getSubsectionOrder()
    {
        return $this->subsectionOrder;
    }
}
