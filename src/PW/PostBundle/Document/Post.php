<?php

namespace PW\PostBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\SoftDelete\SoftDeleteable;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\SerializerBundle\Annotation as API;
use Symfony\Component\Validator\Constraints as Assert;
use PW\PostBundle\Validator as PostAssert;
use PW\ApplicationBundle\Document\AbstractDocument;

/*
Index explanation:
for "all-channels" etc:
    keys={"created"="desc", "category.$id"="asc", "userType"="asc", "isActive"="asc"}
for various feeds queries:
    keys={"target.$id"="asc"}
for spark::viewAction etc:
    keys={"original.$id"="asc"}
for board stream:   
    keys={"board.$id"="asc", "isActive"="asc", "created"="desc"}
*/

/**
 * @MongoDB\Document(collection="posts", repositoryClass="PW\PostBundle\Repository\PostRepository")
 * @MongoDB\Indexes({
 *      @MongoDB\Index(keys={"created"="desc", "category.$id"="asc", "userType"="asc", "isActive"="asc"}, background=true),
 *      @MongoDB\Index(keys={"isActive"="asc", "board.$id"="asc"}, background=true),
 *      @MongoDB\Index(keys={"board.$id"="asc", "isActive"="asc", "created"="desc"}, background=true),
 *      @MongoDB\Index(keys={"isActive"="asc", "target.$id"="asc"}, background=true),
 *      @MongoDB\Index(keys={"isActive"="asc", "createdBy.$id"="asc"}, background=true),
 *      @MongoDB\Index(keys={"isActive"="asc", "parent.$id"="asc"}, background=true),
 *      @MongoDB\Index(keys={"isActive"="asc", "category.$id"="asc", "userType"="asc"}, background=true),
 *      @MongoDB\Index(keys={"target.$id"="asc"}, background=true),
 *      @MongoDB\Index(keys={"original.$id"="asc"}, background=true)
 * })
 *
 * @PostAssert\UniquePost()
 * @API\ExclusionPolicy("none")
 * @API\AccessType("public_method")
 */
class Post extends AbstractDocument implements SoftDeleteable
{
    /**
     * @var string
     * @MongoDB\Id(strategy="ALNUM", options={"pad"="6", "awkwardSafe"=true})
     * @API\Accessor(getter="getId", setter="setId")
     */
    protected $id;

    /**
     * @var \PW\BoardBundle\Document\Board
     * @MongoDB\ReferenceOne(targetDocument="PW\BoardBundle\Document\Board", cascade={"persist"})
     * @Assert\NotBlank(message="Spark must have a Collection.")
     * @Assert\Type(type="PW\BoardBundle\Document\Board", message="The value {{ value }} is not a valid Collection.")
     * @API\Accessor(getter="getSerializedBoard")
     */
    protected $board;

    /**
     * @var \PW\CategoryBundle\Document\Category
     * @MongoDB\ReferenceOne(targetDocument="PW\CategoryBundle\Document\Category")
     * @Assert\NotBlank(message="Spark must have a Category.", groups={"require-category"})
     * @Assert\Type(type="PW\CategoryBundle\Document\Category", message="The value {{ value }} is not a valid Category.", groups={"require-category"})
     * @API\Accessor(getter="getSerializedCategory")
     * @API\SerializedName("channel")
     */
    protected $category;

    /**
     * @var mixed
     * @MongoDB\ReferenceMany(targetDocument="PostActivity", mappedBy="post", criteria={"deleted": null}, sort={"created"="desc"})
     * @API\Exclude
     */
    protected $activity;

    /**
     * @var mixed
     * @MongoDB\ReferenceMany(targetDocument="PostActivity", mappedBy="post", criteria={"deleted": null}, sort={"created"="asc"}, limit=5)
     * @API\SerializedName("recent_activity")
     */
    protected $recentActivity;

    /**
     * @var \PW\PostBundle\Document\Post
     * @MongoDB\ReferenceOne(targetDocument="Post", cascade={"persist"})
     * @API\Accessor(getter="getSerializedOriginal")
     */
    protected $original;

    /**
     * @var \PW\PostBundle\Document\Post
     * @MongoDB\ReferenceOne(targetDocument="Post", cascade={"persist"})
     * @API\Accessor(getter="getSerializedParent")
     */
    protected $parent;

    /**
     * @var mixed
     * @MongoDB\ReferenceOne
     * @API\Accessor(getter="getSerializedTarget")
     * @API\SerializedName("extra")
     */
    protected $target;

    /**
     * @var string
     * @MongoDB\String
     * @Assert\NotBlank(message="Post Description cannot be left blank.")
     */
    protected $description;

    /**
     * @var \PW\AssetBundle\Document\Asset
     * @MongoDB\ReferenceOne(targetDocument="PW\AssetBundle\Document\Asset")
     * @Assert\NotBlank(message="Post must have an Image.")
     */
    protected $image;

    /**
     * @var string
     * @MongoDB\String
     * @API\SerializedName("url")
     */
    protected $link;

    /**
     * @var int
     * @MongoDB\Int
     */
    protected $commentCount = 0;

    /**
     * @var int
     * @MongoDB\Int
     * @API\SerializedName("respark_count")
     */
    protected $repostCount = 0;

    /**
     * Used only for posts that are original (not reposts), this counts all reposts of all descendants.
     *
     * @var int
     * @MongoDB\Int
     * @API\Exclude
     */
    protected $aggregateRepostCount = 0;

    /**
     * @var array
     * @MongoDB\Collection
     */
    protected $tags;

    /**
     * @var string
     * @MongoDB\String
     * @API\Exclude
     */
    protected $contentType = 'image';

    /**
     * The type of user that created this post - user or brand
     *
     * @var string
     * @MongoDB\String
     * @API\Exclude
     */
    protected $userType = 'user';

    /**
     * @var bool
     * @MongoDB\NotSaved
     * @API\Exclude
     */
    protected $postOnFacebook = false;

    /**
     * @MongoDB\Date
     * @API\Exclude
     */
    protected $postedToFacebook;

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
     */
    protected $deleted;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     * @API\Exclude
     */
    protected $deletedBy;

    /**
     * @var bool
     * @MongoDB\Boolean
     * @API\Exclude
     */
    protected $isActive = true;

    /**
     * @MongoDB\NotSaved
     * @API\SerializedName("sparkrebel_url")
     */
    protected $canonicalUrl;

    /**
     * @MongoDB\NotSaved
     * @API\SerializedName("other_boards")
     */
    protected $otherBoards;

    /**
     * @var bool
     * @MongoDB\Boolean
     * @API\Exclude
     */
    protected $isVideoPost;

    /**
     * @var bool
     * @MongoDB\Boolean
     * @API\Exclude
     */
    protected $isCeleb = false;

    /**
     * @var bool
     * @MongoDB\Boolean
     * @API\Exclude
     */
    protected $isCuratorPost = false;

    /**
     * @var bool
     * @MongoDB\Boolean
     * @API\Exclude
     */
    protected $isCuratorPostAlreadyProcessed = false;

    /**
     * @var string - brand | celeb | null
     * @MongoDB\String
     * @API\Exclude
     */
    protected $postType;

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->activity       = new \Doctrine\Common\Collections\ArrayCollection();
        $this->recentActivity = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * @MongoDB\PrePersist
     */
    public function prePersist()
    {
        if ($createdBy = $this->getCreatedBy()) {
            if ($createdBy->isCeleb()) {
                $this->setIsCeleb(true);
            }
            if ($createdBy->hasRole('ROLE_CURATOR')) {
                $this->setIsCuratorPost(true);
                $this->setIsCuratorPostAlreadyProcessed(false);
            }
        }
    }

    /**
     * @param \PW\PostBundle\Document\Post $post
     * @return Post
     */
    public function clonePost(Post $post)
    {
        $this->setParent($post);
        $this->setTarget($post->getTarget());
        $this->setLink($post->getLink());
        $this->setImage($post->getImage());
        $this->setCategory($post->getCategory());
        $this->setDescription($post->getDescription());
        $this->setPostType($post->getPostType());
        return $this;
    }

    /**
     * Set parent
     *
     * @param PW\PostBundle\Document\Post $parent instance
     */
    public function setParent(\PW\PostBundle\Document\Post $parent)
    {
        if ($original = $parent->getOriginal()) {
            $this->original = $original;
        } else {
            $this->original = $parent;
        }
        $this->parent = $parent;
    }

    /**
     * Handled when you call setParent
     */
    public function setOriginal()
    {
        return;
    }

    /**
     * Add activity
     *
     * @param \PW\PostBundle\Document\PostActivity $activity
     */
    public function addActivity(\PW\PostBundle\Document\PostActivity $activity)
    {
        $this->activity[] = $activity;
    }

    /**
     * Get Recent Activity (last 5)
     *
     * @return Doctrine\Common\Collections\Collection $activity
     */
    public function getRecentActivity()
    {
        return $this->recentActivity;
    }

    /**
     * Get Target's Type
     *
     * @return mixed String for type or null if none
     */
    public function getTargetType()
    {
        $target = $this->getTarget();
        if ($target instanceOf \PW\ItemBundle\Document\Item) {
            return 'items';
        } elseif ($target instanceOf \PW\AssetBundle\Document\Asset) {
            return 'assets';
        } elseif ($target instanceOf \PW\OutfitBundle\Document\Outfit) {
            return 'outfits';
        }

        return null;
    }

    /**
     * Increment commentCount
     *
     * @param int $commentCount how many times in total this has been commented on
     */
    public function incCommentCount()
    {
        $this->commentCount++;
    }

    /**
     * Decrement commentCount
     */
    public function decCommentCount()
    {
        $this->commentCount--;
    }

    /**
     * Increment repostCount
     */
    public function incRepostCount()
    {
        $this->repostCount++;
    }

    /**
     * Decrement Repost Count
     */
    public function decrementRepostCount()
    {
        $this->repostCount--;
    }

    /**
     * Increment aggregateRepostCount
     */
    public function incAggregateRepostCount()
    {
        $this->aggregateRepostCount++;
    }

    /**
     * Decrement aggregate Repost Count
     */
    public function decrementAggregateRepostCount()
    {
        $this->aggregateRepostCount--;
    }

    /**
     * Set category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * Set board
     */
    public function setBoard($board)
    {
        $this->board = $board;

        if (!$this->getCategory() && $this->board) {
            $this->setCategory($this->board->getCategory());
        }
    }

    /**
     * @param PW\UserBundle\Document\User $createdBy
     */
    public function setCreatedBy(\PW\UserBundle\Document\User $createdBy = null)
    {
        $this->createdBy = $createdBy;
        if ($this->getCreatedBy()) {
            $this->setUserType($this->getCreatedBy()->getUserType());
        }
    }

    /**
     * @return array
     */
    public function getFacebookAttachment()
    {
        return array(
            'description' => 'check it out on SparkRebel.com',
            'picture'     => $this->getImage()->getUrl(),
            'link'        => null,
            'name'        => $this->getDescription(),
            'caption'     => sprintf('to %s', $this->getBoard()->getName()),
            'message'     => sprintf('%s just sparked this on SparkRebel.com', $this->getCreatedBy()->getName()),
            'icon'        => null,
            'type'        => 'link',
        );
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
     * @param string $url
     */
    public function setCanonicalUrl($url)
    {
        $this->canonicalUrl = $url;
    }

    /**
     * @return string
     */
    public function getCanonicalUrl()
    {
        return $this->canonicalUrl;
    }

    /**
     * @param array $boards
     */
    public function setOtherBoards($boards)
    {
        $this->otherBoards = $boards;
    }

    /**
     * @return array
     */
    public function getOtherBoards()
    {
        return $this->otherBoards;
    }

    /**
     * @param \PW\PostBundle\Document\Post $post
     * @return bool
     */
    public function equals($post = null)
    {
        if (!is_object($post) || !($post instanceOf Post)) {
            return false;
        }

        if ($this->getLink() !== $post->getLink()) {
            // Calculate similarity
            similar_text($this->getLink(), $post->getLink(), $percent);
            if ($percent <= 80) {
                // Less than 80% is considered different
                return false;
            }
        }

        if ($this->getDescription() !== $post->getDescription()) {
            // Calculate similarity
            similar_text($this->getDescription(), $post->getDescription(), $percent);
            if ($percent <= 80) {
                // Less than 80% is considered different
                return false;
            }
        }

        // Category
        $categoryId = null;
        if ($this->getCategory()) {
            $categoryId = $this->getCategory()->getId();
        }

        $postCategoryId = null;
        if ($post->getCategory()) {
            $postCategoryId = $post->getCategory()->getId();
        }

        if ($categoryId !== $postCategoryId) {
            return false;
        }

        // Image
        $imageId = null;
        if ($this->getImage()) {
            $imageId = $this->getImage()->getId();
        }

        $postImageId = null;
        if ($post->getImage()) {
            $postImageId = $post->getImage()->getId();
        }

        if ($imageId !== $postImageId) {
            return false;
        }

        return true;
    }

    //
    // Serialization
    //

    public function setActivity()
    {
    }

    public function setRecentActivity()
    {
    }

    public function getSerializedBoard()
    {
        return array(
            'id'   => $this->getBoard()->getId(),
            'name' => $this->getBoard()->getName(),
        );
    }

    public function getSerializedCategory()
    {
        if ($this->getCategory()) {
            return array(
                'id'   => $this->getCategory()->getId(),
                'name' => $this->getCategory()->getName(),
            );
        }
    }

    public function getSerializedOriginal()
    {
        if ($this->getOriginal()) {
            return array(
                'id' => $this->getOriginal()->getId(),
            );
        }
    }

    public function getSerializedParent()
    {
        if ($this->getParent()) {
            return array(
                'id' => $this->getParent()->getId(),
            );
        }
    }

    public function getSerializedCreatedBy()
    {
        return array(
            'id'   => $this->getCreatedBy()->getId(),
            'name' => $this->getCreatedBy()->getName(),
        );
    }

    public function getSerializedTarget()
    {
        $target = $this->getTarget();
        if (!$target) {
            return;
        }

        $return = array();

        if ($target instanceOf \PW\ItemBundle\Document\Item) {
            $return['type'] = 'item';
            $return['price'] = $target->getPrice();
        } elseif ($target instanceOf \PW\AssetBundle\Document\Asset) {
            $url = $target->getSourceUrl();
            if (!empty($url)) {
                $return['type'] = 'site';
            } else {
                $return['type'] = 'upload';
            }
        }

        return $return;
    }

    /**
     * check if current post is a respark of another $post
     *
     * @param Post $post
     * @return bool
     */
    public function isResparkOf(\PW\PostBundle\Document\Post $post)
    {
        if(!$this->getParent() && !$post->getParent()) {
            return false;
        }
        elseif(!$this->getParent() && $post->getParent()) {
            return ($this->getId() === $post->getOriginal()->getId());
        }
        elseif($this->getParent() && !$post->getParent()) {
            return ($this->getOriginal()->getId() === $post->getId());
        }
        elseif($this->getParent() && $post->getParent()) {
            return ($this->getOriginal()->getId() === $post->getOriginal()->getId());
        }
    }

    /**
     * check if current post is a respark of another $post via asset
     *
     * @param Post $post
     * @return bool
     */
    public function isResparkOfViaAsset(\PW\PostBundle\Document\Post $post)
    {
        return ($this->getImage()->getUrl() === $post->getImage()->getUrl());
    }

    public function getGlobalRepostCount()
    {
        if($this->getOriginal()) {
            return $this->getOriginal()->getAggregateRepostCount();
        }
        elseif($this->getIsReposted()) {
            return $this->getAggregateRepostCount();
        }
    }

    //
    // Doctrine Generation Below
    //

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
     * Get board
     *
     * @return PW\BoardBundle\Document\Board $board
     */
    public function getBoard()
    {
        return $this->board;
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
     * Get activity - filters only active
     *
     * @return Doctrine\Common\Collections\Collection $activity
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * Get original
     *
     * @return PW\PostBundle\Document\Post $original
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * Get parent
     *
     * @return PW\PostBundle\Document\Post $parent
     */
    public function getParent()
    {
        return $this->parent;
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
     * Set image
     *
     * @param PW\AssetBundle\Document\Asset $image
     */
    public function setImage(\PW\AssetBundle\Document\Asset $image)
    {
        $this->image = $image;
    }

    /**
     * Get image
     *
     * @return PW\AssetBundle\Document\Asset $image
     */
    public function getImage()
    {
        return $this->image;
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
     * Set commentCount
     *
     * @param int $commentCount
     */
    public function setCommentCount($commentCount)
    {
        $this->commentCount = $commentCount;
    }

    /**
     * Get commentCount
     *
     * @return int $commentCount
     */
    public function getCommentCount()
    {
        return $this->commentCount;
    }

    /**
     * Set repostCount
     *
     * @param int $repostCount
     */
    public function setRepostCount($repostCount)
    {
        $this->repostCount = $repostCount;
    }

    /**
     * Get repostCount
     *
     * @return int $repostCount
     */
    public function getRepostCount()
    {
        return $this->repostCount;
    }

    /**
     * Set aggregateRepostCount
     *
     * @param int $aggregateRepostCount
     */
    public function setAggregateRepostCount($aggregateRepostCount)
    {
        $this->aggregateRepostCount = $aggregateRepostCount;
    }

    /**
     * Get aggregateRepostCount
     *
     * @return int $aggregateRepostCount
     */
    public function getAggregateRepostCount()
    {
        return $this->aggregateRepostCount;
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
     * Set contentType
     *
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * Get contentType
     *
     * @return string $contentType
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Set userType
     *
     * @param string $userType
     */
    public function setUserType($userType)
    {
        $this->userType = $userType;
    }

    /**
     * Get userType
     *
     * @return string $userType
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * Set postOnFacebook
     *
     * @param string $postOnFacebook
     */
    public function setPostOnFacebook($postOnFacebook)
    {
        $this->postOnFacebook = $postOnFacebook;
    }

    /**
     * Get postOnFacebook
     *
     * @return string $postOnFacebook
     */
    public function getPostOnFacebook()
    {
        return $this->postOnFacebook;
    }

    /**
     * Set postedToFacebook
     *
     * @param date $postedToFacebook
     */
    public function setPostedToFacebook($postedToFacebook)
    {
        $this->postedToFacebook = $postedToFacebook;
    }

    /**
     * Get postedToFacebook
     *
     * @return date $postedToFacebook
     */
    public function getPostedToFacebook()
    {
        return $this->postedToFacebook;
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
     * @return \DateTime $created
     */
    public function getCreated()
    {
        return $this->created;
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
     * Add recentActivity
     *
     * @param PW\PostBundle\Document\PostActivity $recentActivity
     */
    public function addRecentActivity(\PW\PostBundle\Document\PostActivity $recentActivity)
    {
        $this->recentActivity[] = $recentActivity;
    }


    public function wasCreatedBy(\PW\UserBundle\Document\User $user)
    {
      return $this->getCreatedBy()->getId() === $user->getId();
    }

    public function getIsReposted()
    {
      return $this->aggregateRepostCount > 0;
    }

    public function getIsSalesAndPromos()
    {
        if ($category = $this->getCategory()) {
            return $category->getName() === 'Sales & Promos';
        }

        return false;
    }
    /**
     * getIsVideoPost
     *
     * @return
     */
    public function getIsVideoPost()
    {
        return $this->isVideoPost;
    }

    /**
     * setIsVideoPost
     *
     * @param mixed $isVideoPost
     * @return Post
     */
    public function setIsVideoPost($isVideoPost)
    {
        $this->isVideoPost = $isVideoPost;
        return $this;
    }

    public function getVideoHtml()
    {
        return $this->getImage()->getVideoHtml();
    }

    public function setIsCeleb($value)
    {
        $this->isCeleb = (Boolean)$value;
        return $this;
    }

    public function getIsCeleb()
    {
        return (bool) $this->isCeleb;
    }

    public function isCuratorPost()
    {
        return (bool) $this->getIsCuratorPost();
    }

    public function getIsCuratorPost()
    {
        return $this->isCuratorPost;
    }

    public function setIsCuratorPost($value)
    {
        $this->isCuratorPost = (Boolean)$value;
        return $this;
    }

    public function getIsCuratorPostAlreadyProcessed()
    {
        return $this->isCuratorPostAlreadyProcessed;
    }

    public function setIsCuratorPostAlreadyProcessed($value)
    {
        $this->isCuratorPostAlreadyProcessed = (Boolean)$value;
        return $this;
    }

    
    public function getPostType() {
        return $this->postType;
    }
    
    
    public function setPostType($postType) {
        $this->postType = $postType;    
        return $this;
    }
}
