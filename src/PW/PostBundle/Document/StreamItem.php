<?php

namespace PW\PostBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\SerializerBundle\Annotation as API;

/**
 * @MongoDB\Document(collection="stream_items") 
 *
 * @API\ExclusionPolicy("none")
 * @API\AccessType("public_method")
 */
class StreamItem 
{
    /**
     * @MongoDB\Id
     * @API\Accessor(getter="getId", setter="setId")
     * @API\SerializedName("id")
     */
    protected $id;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")          
     */
    protected $user;

    /**
     * @var \PW\PostBundle\Document\Post
     * @MongoDB\ReferenceOne(targetDocument="Post")     
     */
    protected $post;

    /**
     * @Gedmo\Timestampable(on="create")
     * @MongoDB\Date
     */
    protected $created;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $type;

    /**
     * @var int
     * @MongoDB\Int
     */
    protected $score;

    
    public function getId() {
        return $this->id;
    }
      
    public function setId($id) {
        $this->id = $id;    
        return $this;
    }

    public function getUser() {
        return $this->user;
    }
    
    public function setUser($user) {
        $this->user = $user;
    
        return $this;
    }

    public function getPost() {
        return $this->post;
    }
    
    public function setPost($post) {
        $this->post = $post;
    
        return $this;
    }

    public function setCreated($created)
    {
        $this->created = $created;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function getType() {
        return $this->type;
    }
    
    public function setType($type) {
        $this->type = $type;    
        return $this;
    }

    public function getScore() {
        return $this->score;
    }
    
    public function setScore($score) {
        $this->score = $score;    
        return $this;
    }
}