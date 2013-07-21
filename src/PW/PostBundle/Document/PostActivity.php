<?php

namespace PW\PostBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\SoftDelete\SoftDeleteable;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\SerializerBundle\Annotation as API;
use PW\ApplicationBundle\Document\AbstractDocument;

/**
 * @MongoDB\Document(collection="posts_activity", repositoryClass="PW\PostBundle\Repository\PostActivityRepository")
 * @MongoDB\InheritanceType("SINGLE_COLLECTION")
 * @MongoDB\DiscriminatorField(fieldName="type")
 * @MongoDB\DiscriminatorMap({"activity"="PostActivity", "comment"="PostComment"})
 * @MongoDB\Indexes({
 *      @MongoDB\UniqueIndex(keys={"post.$id"="asc", "createdBy.$id"="asc", "content"="asc"}, background=true)
 * })
 * @API\ExclusionPolicy("none")
 */
class PostActivity extends AbstractDocument implements SoftDeleteable
{
    /**
     * @MongoDB\Id
     * @API\Accessor(getter="getId", setter="setId")
     * @API\SerializedName("id")
     */
    protected $id;

    /**
     * @var \PW\PostBundle\Document\Post
     * @MongoDB\ReferenceOne(targetDocument="Post", inversedBy="activity", cascade={"persist"})
     * @API\Exclude
     */
    protected $post;

    /**
     * @MongoDB\ReferenceMany(criteria={"deleted": null}, sort={"created"="desc"})
     */
    protected $subactivity;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $content;

    /**
     * @var bool
     * @MongoDB\Boolean
     * @API\Exclude
     */
    protected $isActive = true;

    /**
     * @Gedmo\Timestampable(on="create")
     * @MongoDB\Date
     */
    protected $created;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     * @API\Accessor(getter="getSerializedCreatedBy")
     * @API\SerializedName("user")
     */
    protected $createdBy;

    /**
     * @Gedmo\Timestampable(on="update")
     * @MongoDB\Date
     * @API\Exclude
     */
    protected $modified;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     * @API\Exclude
     */
    protected $modifiedBy;

    /**
     * @MongoDB\Date
     * @API\Exclude
     */
    protected $deleted;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     * @API\Exclude
     */
    protected $deletedBy;

    /**
     * @var string
     * @API\SerializedName("type")
     */
    protected $type;

    public function __construct($data = array())
    {
        $this->subactivity = new \Doctrine\Common\Collections\ArrayCollection();
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'activity';
    }

    /**
     * @return void
     */
    public function setType()
    {
        return;
    }

    //
    // Serialization
    //

    public function setSubactivity()
    {
    }

    public function getSerializedCreatedBy()
    {
        return array(
            'id'   => $this->getCreatedBy()->getId(),
            'name' => $this->getCreatedBy()->getName(),
        );
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
     * Set post
     *
     * @param PW\PostBundle\Document\Post $post
     */
    public function setPost(\PW\PostBundle\Document\Post $post)
    {
        $this->post = $post;
    }

    /**
     * Get post
     *
     * @return PW\PostBundle\Document\Post $post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Add subactivity
     *
     * @param $subactivity
     */
    public function addSubactivity($subactivity)
    {
        $this->subactivity[] = $subactivity;
    }

    /**
     * Get subactivity
     *
     * @return Doctrine\Common\Collections\Collection $subactivity
     */
    public function getSubactivity()
    {
        return $this->subactivity;
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
}
