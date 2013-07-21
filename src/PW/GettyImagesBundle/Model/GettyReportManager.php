<?php

namespace PW\GettyImagesBundle\Model;

use PW\ApplicationBundle\Model\AbstractManager,
    PW\GettyImagesBundle\Document\GettyReport,
    PW\UserBundle\Document\User;

class GettyReportManager extends AbstractManager
{
    /**
     * @param array $data
     * @return \PW\GettyImagesBundle\Document\GettyReport
     */
    public function create(array $data = array())
    {
        /* @var $object \PW\GettyImagesBundle\Document\GettyReport */
        $object = parent::create($data);
        $object->setStatus('new');
        $object->setTextStatus('Newly created - waiting to start generating');
        //$object->setStartDate(new \DateTime()); 
        return $object;
    }

    /**
     * @return mixed
     */    
    public function canGenerateNew()
    {
        $newest = $this->getNewestOne();
        if ($newest) {
            if ($newest->getStatus() != 'sent') { 
                return false;
            }
        }
        return true;
    }
    
    /**
     * @return mixed
     */
    public function findAll()
    {
        return $this->getRepository()->findAll()->sort('created', 'desc')->getQuery()->execute();
    }
    
    /**
     * @return mixed
     */    
    public function getNewestOne()
    {
        $qb = $this->getRepository()->findAll();
        $result = $qb->sort('created', 'desc')
            ->limit(1)
            ->getQuery()->execute();
        return $result->getNext();   
    }
    
}
