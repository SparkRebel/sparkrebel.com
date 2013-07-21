<?php

namespace PW\AssetBundle\Document;

use PW\ApplicationBundle\Document\AbstractDocument,
    Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    Gedmo\Mapping\Annotation as Gedmo;

/**
 * Source
 *
 * @MongoDB\Document(collection="sources", repositoryClass="PW\AssetBundle\Repository\SourceRepository")
 */
class Source extends AbstractDocument
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * assetCount
     *
     * @MongoDB\Int
     */
    protected $assetCount = 0;

    /**
     * @Gedmo\Timestampable(on="create")
     * @MongoDB\Date
     */
    protected $created;

    /**
     * @MongoDB\Date
     */
    protected $deleted;

    /**
     * @MongoDB\Boolean
     */
    protected $isActive;

    /**
     * @Gedmo\Timestampable(on="update")
     * @MongoDB\Date
     */
    protected $modified;

    /**
     * domain name - e.g. foo.com
     *
     * @MongoDB\String
     */
    protected $name;

    /**
     * postCount
     *
     * @MongoDB\Int
     */
    protected $postCount = 0;

    /**
     * score
     *
     * @MongoDB\Int
     */
    protected $score;

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
     * incAssetCount
     *
     * @return int how many assets there now are
     */
    public function incAssetCount()
    {
        return ++$this->assetCount;
    }

    /**
     * Set assetCount
     *
     * @param int $assetCount
     */
    public function setAssetCount($assetCount)
    {
        $this->assetCount = $assetCount;
    }

    /**
     * Get assetCount
     *
     * @return int $assetCount
     */
    public function getAssetCount()
    {
        return $this->assetCount;
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
     * Set score
     *
     * @param int $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * Get score
     *
     * @return int $score
     */
    public function getScore()
    {
        return $this->score;
    }
}
