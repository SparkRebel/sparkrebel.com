<?php

namespace PW\BannerBundle\Model;

use PW\ApplicationBundle\Model\AbstractManager,
    PW\BannerBundle\Document\Banner,
    PW\UserBundle\Document\User;

class BannerManager extends AbstractManager
{
    /**
     * @param array $data
     * @return \PW\BannerBundle\Document\Banner
     */
    public function create(array $data = array())
    {
        /* @var $banner \PW\BannerBundle\Document\Banner */
        $banner = parent::create($data);
        //$banner->setDescription('A banner');
        $banner->setStartDate(new \DateTime());
        $banner->setEndDate(new \DateTime());  
        return $banner;
    }
    
    /**
     * @return mixed
     */
    public function findAllActive()
    {
        return $this->getRepository()->findAllActive()->getQuery()->execute();
    }
    
    /**
     * @return mixed
     */    
    public function findAllActiveAndRunning()
    {
        return $this->getRepository()->findAllActiveAndRunning()->getQuery()->execute();
    }
    
}
