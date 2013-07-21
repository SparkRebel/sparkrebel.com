<?php

namespace PW\TaggingBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use PW\CategoryBundle\Document\Category;
/**
 * @MongoDB\Document(collection="taggings")
 */
class Tagging
{
    /**
     * @var string
     * @MongoDB\Id
     */
    protected $id;


    /**
     * @var string
     * @MongoDB\String
     * @Assert\NotBlank(message="Tagging name cannot be left blank.")
     */
    protected $name;
    
   
    /**
     * @param array $data
     */
    public function __construct()
    {
        
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }
    
    public function setName($name) {
        $this->name = $name;
        return $this;
    }            
    
}
