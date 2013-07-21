<?php

namespace PW\TaggingBundle\Extension;
use Doctrine\ODM\MongoDB\DocumentManager;
class Taggings extends \Twig_Extension
{
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }
    
    public function getFunctions()
    {
        return array(
            'all_taggings'  => new \Twig_Function_Method($this, 'getAllTaggings'),
        );
    }


    public function getAllTaggings()
    {        
        return $this->dm->getRepository('PWTaggingBundle:Tagging')->findAll();
    }

   
    public function getName()
    {
        return 'pw_taggings';
    }
}
