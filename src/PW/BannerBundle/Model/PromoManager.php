<?php

namespace PW\BannerBundle\Model;

use PW\ApplicationBundle\Model\AbstractManager,
    PW\BannerBundle\Document\Promo,
    PW\UserBundle\Document\User;

class PromoManager extends AbstractManager
{
    /**
     * @param array $data
     * @return \PW\BannerBundle\Document\Promo
     */
    public function create(array $data = array())
    {
        /* @var $promo \PW\BannerBundle\Document\Promo */
        $promo = parent::create($data);
        //$promo->setDescription('A promo');
        $promo->setStartDate(new \DateTime());
        $promo->setEndDate(new \DateTime());  
        $promo->setIsUrlTargetBlank(true);  
        $promo->setInMyBrands(true);  
        $promo->setInShop(true);  
        $promo->setInMyStream(false);  
        $promo->setInAllCategories(false);  
        return $promo;
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
    public function getPromosForStream($inWhat, $userToFilterByFollows = null, $brandId = null, $limit=null, $skip=null)
    {
        $qb = $this->getRepository()->findAllActiveAndRunning();
        if ($inWhat) {
            $qb->field('in'.$inWhat)->equals(true);
        }
        if ($brandId) {
            // filter by $brandId
            $qb->field('user.$id')->equals(new \MongoId($brandId));
        }
        if ($userToFilterByFollows) {
            // filter by Brands and Merchants of User
            
            $follows_qb = $this->dm->getRepository('PWUserBundle:Follow')->findFollowingByUser($userToFilterByFollows);
            $follows_qb->field('target.type')->in(array('brand','merchant'));
            $follows = $follows_qb->getQuery()->execute();
            $user_ids = array();
            foreach ($follows as $f) { 
                $user_ids[] = new \MongoId($f->getTarget()->getId());
            }
            $qb->field('user.$id')->in($user_ids);
        }
        if ($limit) {
            $qb->limit($limit);
        }
        if ($skip) {
            $qb->skip($skip);
        }
        return $qb->sort('created', 'desc')
            ->getQuery()->execute();
    }
    
}
