<?php

namespace PW\BoardBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\SoftDelete\SoftDeleteable;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Validator\Constraints as Assert;
use PW\BoardBundle\Validator as BoardAssert;
use PW\ApplicationBundle\Document\AbstractDocument;
use JMS\SerializerBundle\Annotation as API;

/**
 * @MongoDB\Document(collection="boards", repositoryClass="PW\BoardBundle\Repository\BoardRepository")
 * @MongoDB\Indexes({
 *      @MongoDB\UniqueIndex(keys={"isActive"="asc", "name"="asc", "category.$id"="asc", "createdBy.$id"="asc"}, safe=true, background=true)
 * })
 * @BoardAssert\UniqueBoard()
 * @API\ExclusionPolicy("none")
 * @API\AccessType("public_method")
 */
class Board extends AbstractDocument implements SoftDeleteable
{
    /**
     * @var string
     * @MongoDB\Id
     * @API\Accessor(getter="getId", setter="setId")
     */
    protected $id;

    /**
     * @var \PW\CategoryBundle\Document\Category
     * @MongoDB\ReferenceOne(targetDocument="PW\CategoryBundle\Document\Category")
     * @Assert\NotBlank(message="Collection must have a Category.", groups={"require-category"})
     * @Assert\Type(type="PW\CategoryBundle\Document\Category", message="The value {{ value }} is not a valid Category.", groups={"require-category"})
     * @API\Accessor(getter="getSerializedCategory")
     * @API\SerializedName("channel")
     */
    protected $category;

    /**
     * @var string
     * @MongoDB\String
     * @Assert\NotBlank(message="Collection Name cannot be left blank.")
     */
    protected $name;

    /**
     * @var string
     * @Assert\MaxLength(140)
     * @MongoDB\String
     */
    protected $description;

    /**
     * @var string
     * @Gedmo\Slug(fields={"name"})
     * @MongoDB\String
     */
    protected $slug;

    /**
     * @var array
     * @MongoDB\Collection
     * @API\Exclude
     */
    protected $tags;

    /**
     * @var int
     * @MongoDB\Int
     * @API\Exclude
     */
    protected $postCount = 0;

    /**
     * @var int
     * @MongoDB\Int
     * @API\Exclude
     */
    protected $followerCount = 0;

    /**
     * Array of images used when displaying a board
     *
     * The first item in the array is the OLDEST
     *
     * @MongoDB\ReferenceMany(targetDocument="PW\AssetBundle\Document\Asset")
     * @API\Accessor(getter="getImages", setter="setImages")
     * @API\Exclude
     */
    protected $images;

    /**
     * @var bool
     * @MongoDB\Boolean
     * @API\Exclude
     */
    protected $isActive = true;

    /**
     * @var bool
     * @MongoDB\Boolean
     * @API\Exclude
     */
    protected $isPublic = false;

    /**
     * @var bool
     * @MongoDB\Boolean
     * @API\Exclude
     */
    protected $isSystem = false;

    /**
     * @Gedmo\Timestampable(on="create")
     * @MongoDB\Date
     */
    protected $created;

    /**
     * @var \PW\UserBundle\Document\User
     * @Assert\NotBlank(message="Collection must have a Category.", groups={"require-owner"})
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
     * @var int
     * @MongoDB\Int
     * @API\Exclude
     */
    protected $adminScore = 0;

    /**
     * @MongoDB\NotSaved
     * @API\Accessor(getter="getSerializedPosts")
     * @API\SerializedName("sparks")
     */
    protected $posts;

    /**
     * @var \PW\AssetBundle\Document\Asset
     * @MongoDB\ReferenceOne(targetDocument="PW\AssetBundle\Document\Asset")
     */
    protected $icon;
    
    /**
     * @MongoDB\Date
     * @API\Exclude
     */
    protected $notifiedFbAt;

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
        parent::__construct($data);
    }

    /**
     * Inc followerCount
     */
    public function incFollowerCount()
    {
        $this->followerCount++;
    }

    /**
     * Dec followerCount
     */
    public function decFollowerCount()
    {
        $this->followerCount--;
    }

    /**
     * Inc postCount
     */
    public function incPostCount()
    {
        $this->postCount++;
    }

    /**
     * Decrement Post count
     */
    public function decrementPostCount()
    {
        $this->postCount--;
    }

    /**
     * Add an image
     *
     * @param PW\AssetBundle\Document\Asset $image
     */
    public function addImages(\PW\AssetBundle\Document\Asset $image)
    {
        if($this->images->contains($image)) {
            return;
        }
        $this->images[] = $image;

        if (count($this->images) > 4) {
            $this->images = $this->images->slice(-4);
        }
    }

    /**
     * Remove an image
     *
     * @param PW\AssetBundle\Document\Asset $image
     */
    public function removeImage(\PW\AssetBundle\Document\Asset $image)
    {
        if($this->images->contains($image))
          $this->images->removeElement($image);
    }

    /**
     * Get images
     *
     * Note that images are stored oldest to newest - index 3 is the newest post image
     *
     * @return Doctrine\Common\Collections\Collection $images
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * getImagesReversed
     *
     * @return Doctrine\Common\Collections\Collection $images
     */
    public function getImagesReversed()
    {
        $return = new \Doctrine\Common\Collections\ArrayCollection();
        foreach (array_reverse($this->images->getValues()) as $val) {
            if($return->contains($val) === false)
              $return->add($val);
        }
        return $return;
    }

    /**
     * Checks if user is allowed to post to board
     *
     * @param \PW\UserBundle\Document\User $user
     * @return boolean $canPost
     */
    public function userCanPost(\PW\UserBundle\Document\User $user)
    {
        if ($this->getIsPublic()) {
            return true;
        }
        if ($this->getCreatedBy() == $user) {
            return true;
        }
        return false;
    }

    /**
     * @param type $posts
     */
    public function setPosts($posts)
    {
        $this->posts = $posts;
    }

    /**
     * @return type
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;

        if ($category = $this->getCategory()) {
            $this->setSlug($category->getName() . '-' . $name);
        }
    }

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = Urlizer::transliterate($slug);
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
     * Set deleted
     *
     * @param \DateTime $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted  = $deleted;
        $this->isActive = false;
    }

    /**
     * Before committing to the db
     *
     * If we've got too many images, pop the oldest images
     *
     * @MongoDB\PreUpdate
     */
    public function preUpdate()
    {
        if (!$this->getDeleted()) {
            while (count($this->images) > 4) {
                $this->images->first();
                $firstKey = $this->images->key();
                $this->images->remove($firstKey);
            }
        }
    }

    /**
     * @param \PW\UserBundle\Document\User $user
     * @return bool
     */
	public function wasCreatedBy(\PW\UserBundle\Document\User $user = null)
    {
        if (!$user) {
            return false;
        }

        if ($createdBy = $this->getCreatedBy()) {
            return ($createdBy->getId() == $user->getId());
        }

        return false;
    }

    //
    // Serialization
    //

    public function getSerializedCategory()
    {
        if ($this->getCategory()) {
            return array(
                'id'   => $this->getCategory()->getId(),
                'name' => $this->getCategory()->getName(),
            );
        }
    }

    public function getSerializedCreatedBy()
    {
        if ($this->getCreatedBy()) {
            return array(
                'id'   => $this->getCreatedBy()->getId(),
                'name' => $this->getCreatedBy()->getName(),
            );
        }
    }

    public function getSerializedPosts()
    {
        $posts = null;
        if (!empty($this->posts)) {
            $posts = array();
            foreach ($this->posts as $post /* @var $post \PW\PostBundle\Document\Post */) {
                $posts[] = array(
                    'id' => $post->getId(),
                    'description' => $post->getDescription(),
                    'image' => $post->getImage(),
                    'user' => $post->getCreatedBy(),
                );
            }
        }
        return $posts;
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
     * Set category
     *
     * @param PW\CategoryBundle\Document\Category $category
     */
    public function setCategory(\PW\CategoryBundle\Document\Category $category = null)
    {
        $this->category = $category;
    }

    /**
     * Get category
     *
     * @return PW\CategoryBundle\Document\Category $category
     */
    public function getCategory()
    {
        return $this->category;
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
     * getDescription
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * setDescription
     *
     * @param string $description
     * @return Board
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
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
     * Set postCount
     *
     * @param int $postCount
     */
    public function setPostCount($postCount)
    {
        $this->postCount = $postCount;
    }

    /**
     * Get postCount
     *
     * @return int $postCount
     */
    public function getPostCount()
    {
        return $this->postCount;
    }

    /**
     * Set followerCount
     *
     * @param int $followerCount
     */
    public function setFollowerCount($followerCount)
    {
        $this->followerCount = $followerCount;
    }

    /**
     * Get followerCount
     *
     * @return int $followerCount
     */
    public function getFollowerCount()
    {
        return $this->followerCount;
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
     * Set isPublic
     *
     * @param boolean $isPublic
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;
    }

    /**
     * Get isPublic
     *
     * @return boolean $isPublic
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }

    /**
     * Set isSystem
     *
     * @param boolean $isSystem
     */
    public function setIsSystem($isSystem)
    {
        $this->isSystem = $isSystem;
    }

    /**
     * Get isSystem
     *
     * @return boolean $isSystem
     */
    public function getIsSystem()
    {
        return $this->isSystem;
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
     * Set adminScore
     *
     * @param int $adminScore
     */
    public function setAdminScore($adminScore)
    {
        $this->adminScore = $adminScore;
    }

    /**
     * Get adminScore
     *
     * @return int $adminScore
     */
    public function getAdminScore()
    {
        return $this->adminScore;
    }



    /**
     * Set icon
     *
     * @param PW\AssetBundle\Document\Asset $icon
     */
    public function setIcon(\PW\AssetBundle\Document\Asset $icon)
    {
        $this->icon = $icon;
    }

    /**
     * Get icon
     *
     * @return PW\AssetBundle\Document\Asset $icon
     */
    public function getIcon()
    {
        return $this->icon;
    }
	
    /**
     * Set notifiedFbAt
     *
     * @param date $notifiedFbAt
     */
    public function setNotifiedFbAt($notifiedFbAt)
    {
        $this->notifiedFbAt = $notifiedFbAt;
    }

    /**
     * Get notifiedFbAt
     *
     * @return date $notifiedFbAt
     */
    public function getNotifiedFbAt()
    {
        return $this->notifiedFbAt;
    }	
}
