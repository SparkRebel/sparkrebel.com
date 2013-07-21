<?php

namespace PW\UserBundle\Document\User;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use PW\ApplicationBundle\Document\AbstractDocument;

/**
 * @MongoDB\EmbeddedDocument
 */
class Counts extends AbstractDocument
{
    /**
     * @var int
     * @MongoDB\Int
     */
    protected $boards = 0;

    /**
     * @var int
     * @MongoDB\Int
     */
    protected $posts = 0;

    /**
     * @var int
     * @MongoDB\Int
     */
    protected $reposts = 0;

    /**
     * @var bool
     * @MongoDB\NotSaved
     */
    protected $isActive;

    /**
     * @var bool
     * @MongoDB\NotSaved
     */
    protected $deleted;

    /**
     * Determine if all counts for a User are zero
     *
     * @return boolean
     */
    public function isEmpty()
    {
        $fields = array('boards', 'posts', 'reposts');
        foreach ($fields as $field) {
            $method = 'get' . ucfirst($field);
            if ($this->$method() > 0) {
                return false;
            }
        }

        return true;
    }

    public function incrementBoards()
    {
        $this->setBoards($this->boards + 1);
    }

    public function decrementBoards()
    {
        $this->setBoards($this->boards - 1);
    }

    public function incrementPosts()
    {
        $this->setPosts($this->posts + 1);
    }

    public function decrementPosts()
    {
        $this->setPosts($this->posts - 1);
    }

    public function incrementReposts()
    {
        $this->setReposts($this->reposts + 1);
    }

    public function decrementReposts()
    {
        $this->setReposts($this->reposts - 1);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();
        unset($data['isActive']);
        unset($data['deleted']);
        return $data;
    }

    /**
     * @return string
     */
    public function getAdminValue()
    {
        return array(
            'boards'  => $this->boards,
            'posts'   => $this->posts,
            'reposts' => $this->reposts,
        );
    }

    /**
     * Set boards
     *
     * @param increment $boards
     */
    public function setBoards($boards)
    {
        $this->boards = $boards;
        if ($this->boards < 0) {
            $this->boards = 0;
        }
    }

    /**
     * Set posts
     *
     * @param increment $posts
     */
    public function setPosts($posts)
    {
        $this->posts = $posts;
        if ($this->posts < 0) {
            $this->posts = 0;
        }
    }

    /**
     * Set reposts
     *
     * @param increment $reposts
     */
    public function setReposts($reposts)
    {
        $this->reposts = $reposts;
        if ($this->reposts < 0) {
            $this->reposts = 0;
        }
    }

    //
    // Doctrine Generation Below
    //

    /**
     * Get boards
     *
     * @return increment $boards
     */
    public function getBoards()
    {
        return $this->boards;
    }

    /**
     * Get posts
     *
     * @return increment $posts
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * Set isActive
     *
     * @param string $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * Get isActive
     *
     * @return string $isActive
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set deleted
     *
     * @param string $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * Get deleted
     *
     * @return string $deleted
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Get reposts
     *
     * @return increment $reposts
     */
    public function getReposts()
    {
        return $this->reposts;
    }
    /**
     * @var date $created
     */
    protected $created;

    /**
     * @var date $modified
     */
    protected $modified;


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
