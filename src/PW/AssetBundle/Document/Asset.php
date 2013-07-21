<?php

namespace PW\AssetBundle\Document;

use PW\ApplicationBundle\Document\AbstractDocument,
    Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    Imagine\Gd\Imagine,
    Gedmo\Mapping\Annotation as Gedmo,
    JMS\SerializerBundle\Annotation as API,
    PW\PostBundle\Model\VideoSparker;;

/*
Index explanation:
for getty operations?:
    keys={"source"="asc"}
*/

/**
 * @MongoDB\Document(collection="assets", repositoryClass="PW\AssetBundle\Repository\AssetRepository")
 * @MongoDB\Indexes({
 *      @MongoDB\UniqueIndex(keys={"hash"="asc"}, background=true),
 *      @MongoDB\Index(keys={"sourceUrl"="asc"}, background=true),
 *      @MongoDB\Index(keys={"source"="asc"}, background=true)
 * })
 * @API\ExclusionPolicy("none")
 * @API\AccessType("public_method")
 */
class Asset extends AbstractDocument
{
    /**
     * @MongoDB\Id
     * @API\Accessor(getter="getId", setter="setId")
     */
    protected $id;

    /**
     * @MongoDB\Float
     * @API\Exclude
     */
    protected $aspectRatio;

    /**
     * @Gedmo\Timestampable(on="create")
     * @MongoDB\Date
     * @API\Exclude
     */
    protected $created;

    /**
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     * @API\Exclude
     */
    protected $createdBy;

    /**
     * @MongoDB\Date
     * @API\Exclude
     */
    protected $deleted;

    /**
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     * @API\Exclude
     */
    protected $deletedBy;

    /**
     * @MongoDB\String
     * @API\Exclude
     */
    protected $description;

    /**
     * @MongoDB\Boolean
     * @API\Exclude
     */
    protected $isActive;

    /**
     * @MongoDB\Hash
     * @API\Exclude
     */
    protected $meta;

    /**
     * @MongoDB\String
     * @API\Exclude
     */
    protected $hash;

    /**
     * @MongoDB\String
     * @API\Exclude
     */
    protected $host;

    /**
     * @Gedmo\Timestampable(on="update")
     * @MongoDB\Date
     * @API\Exclude
     */
    protected $modified;

    /**
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     * @API\Exclude
     */
    protected $modifiedBy;

    /**
     * Contains width and height for images
     *
     * @MongoDB\Hash
     * @API\Exclude
     */
    protected $originalDimensions;

    /**
     * @MongoDB\String
     * @API\Exclude
     */
    protected $source;

    /**
     * @MongoDB\Collection
     * @API\Exclude
     */
    protected $tags;

    /**
     * @MongoDB\String
     */
    protected $url;
    
    /**
     * @MongoDB\String
     */
    protected $thumbsExtension;

    /**
     * if true, then it will detect PNG format, if false - always JPG
     *
     * @MongoDB\Boolean
     */
    protected $allowPng;
    
    /**
     * The domain this image was plucked from
     *
     * @MongoDB\String
     * @API\Exclude
     */
    protected $sourceDomain;

    /**
     * The page this image was plucked from
     *
     * @MongoDB\String
     * @API\Exclude
     */
    protected $sourcePage;

    /**
     * If it's an item or uploaded by url - this stores the url for the original
     * This is one of the fields checked when looking for duplicates
     *
     * @MongoDB\String
     * @API\Exclude
     */
    protected $sourceUrl;

    /**
     * @MongoDB\String
     * @API\Exclude
     */
    protected $type = 'user';
    
    
    /**
     * @MongoDB\String
     * @API\Exclude
     */
    protected $videoCode;
    
    
    /**
     * @MongoDB\Boolean
     * @API\Exclude
     */
    protected $fromGetty;

    /**
     * @MongoDB\Hash
     * @API\Exclude
     */
    protected $gettyData;

    /**
     * @return string
     */
    public function getAdminValue()
    {
        return $this->getUrl();
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
     * Set meta
     *
     * @param hash $meta
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
    }

    /**
     * Get meta
     *
     * @return hash $meta
     */
    public function getMeta()
    {
        return $this->meta;
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
        if (!$this->url) {
            return $this->getSourceUrl();
        }
        return $this->url;
    }
    
    /**
     * Set thumbsExtension
     *
     * @param string $thumbsExtension
     */
    public function setThumbsExtension($thumbsExtension)
    {
        $this->thumbsExtension = $thumbsExtension;
    }

    /**
     * Get thumbsExtension
     *
     * @return string $thumbsExtension
     */
    public function getThumbsExtension()
    {
        if (strlen($this->thumbsExtension)<1) { 
            return 'png'; // for old assets
        }
        return $this->thumbsExtension;
    }
    
    /**
     * Set allowPng - if true, then it will detect PNG format, if false - always JPG
     *
     * @param boolean $allowPng
     */
    public function setAllowPng($allowPng)
    {
        $this->allowPng = $allowPng;
    }

    /**
     * Get allowPng
     *
     * @return boolean $allowPng
     */
    public function getAllowPng()
    {
        return $this->allowPng;
    }
    
    /**
     * Set hash
     *
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * Get hash
     *
     * @return string $hash
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set host
     *
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Get host
     *
     * @return string $host
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set sourceDomain
     *
     * @param string $sourceDomain
     */
    public function setSourceDomain($sourceDomain)
    {
        $this->sourceDomain = $sourceDomain;
    }

    /**
     * Get sourceDomain
     *
     * @return string $sourceDomain
     */
    public function getSourceDomain()
    {
        return $this->sourceDomain;
    }

    /**
     * Set sourcePage
     *
     * @param string $sourcePage
     */
    public function setSourcePage($sourcePage)
    {
        $this->sourcePage = $sourcePage;
    }

    /**
     * Get sourcePage
     *
     * @return string $sourcePage
     */
    public function getSourcePage()
    {
        return $this->sourcePage;
    }

    /**
     * Set sourceUrl
     *
     * @param string $sourceUrl
     */
    public function setSourceUrl($sourceUrl)
    {
        $pathInfo = parse_url($sourceUrl);
        if ($pathInfo) {
            $this->setSourceDomain(ltrim($pathInfo['host'], 'www.'));
        }
        $this->sourceUrl = $sourceUrl;
    }

    /**
     * Get sourceUrl
     *
     * @return string $sourceUrl
     */
    public function getSourceUrl()
    {
        return $this->sourceUrl;
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
     * Set aspectRatio
     *
     * @param int $aspectRatio
     */
    public function setAspectRatio($aspectRatio)
    {
        $this->aspectRatio = $aspectRatio;
    }

    /**
     * Get aspectRatio
     *
     * @return int $aspectRatio
     */
    public function getAspectRatio()
    {
        return $this->aspectRatio;
    }

    /**
     * Set originalDimensions
     *
     * @param hash $originalDimensions
     */
    public function setOriginalDimensions($originalDimensions)
    {
        $this->originalDimensions = $originalDimensions;
    }

    /**
     * Get originalDimensions
     *
     * @return hash $originalDimensions
     */
    public function getOriginalDimensions()
    {
        return $this->originalDimensions;
    }
    
    /**
     * getVideoCode
     *
     * @return
     */
    public function getVideoCode()
    {
        return $this->videoCode;
    }

    /**
     * setVideoCode
     *
     * @param mixed $videoCode
     * @return Asset
     */
    public function setVideoCode($videoCode)
    {
        $this->videoCode = $videoCode;
        return $this;
    }

    

    public function getFromGetty()
    {
        return $this->fromGetty;
    }

    public function setFromGetty($newFromGetty)
    {
        $this->fromGetty = $newFromGetty;
        return $this;
    }
    


    public function getGettyData()
    {
        return $this->gettyData;
    }
    

    public function setGettyData($newGettyData)
    {
        $this->gettyData = $newGettyData;
        return $this;
    }

    /**
     * setDimensions
     *
     * Set width, height and aspect ratio data. If no image instance is passed - the url for the
     * current asset will be used if it is a local path
     *
     * @param mixed $image path to image, imagine image instance or null to use this asset's url
     *
     * @return true on success, false if the dimensions could not be set
     */
    public function setDimensions($image = null)
    {
        if (!is_object($image)) {
            if (!$image) {
                $path = $this->getUrl();
                if ($path && $path[0] !== '/') {
                    return false;
                }
            } elseif (is_string($image)) {
                $path = $image;
            }
            if (!file_exists($path)) {
                $path = dirname(dirname(dirname(dirname(__DIR__)))) . '/web' . $path;
            }

            $imagine = new Imagine();
            $image = $imagine->open($path);
        }

        $size = $image->getSize();
        $width = $size->getWidth();
        $height = $size->getHeight();
        $ratio = round($width / $height, 3);
        $this->setOriginalDimensions(compact('width', 'height'));
        $this->setAspectRatio($ratio);

        return true;
    }
    
    public function getIsVideo()
    {
        return !is_null($this->videoCode);
    }
    
    public function getVideoHtml()
    {
        return VideoSparker::getCodeForVideoAsset($this);
    }
    
    /**
     * Things to do before inserting
     *
     * @MongoDB\PrePersist
     */
    public function prePersist()
    {
        if (!$this->host) {
            $this->setHost(trim(`hostname`));
        }
        if (!$this->aspectRatio || !$this->originalDimensions) {
            $this->setDimensions();
        }
    }

    public function isGetty()
    {
        return strncmp($this->source, 'getty:', 6) === 0 || $this->fromGetty === true;
    }

    public function getIsGetty()
    {
        return $this->isGetty();
    }

    /**
     * getAdminData
     *
     */
    public function getAdminData()
    {
        $return = parent::getAdminData();

        $return['aspectRatio'] = $this->getAspectRatio();
        $return['description'] = $this->getDescription();
        $return['isActive'] = $this->getIsActive();
        $return['meta'] = $this->getMeta();
        $return['hash'] = $this->getHash();
        $return['host'] = $this->getHost();
        $return['originalDimensions'] = $this->getOriginalDimensions();
        $return['source'] = $this->getSource();
        $return['sourceDomain'] = $this->getSourceDomain();
        $return['sourcePage'] = $this->getSourcePage();
        $return['sourceUrl'] = $this->getSourceUrl();
        $return['tags'] = $this->getTags();
        $return['type'] = $this->getType();
        $return['url'] = $this->getUrl();

        return $return;
    }

}
