<?php

namespace PW\BannerBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class BannerRepository extends DocumentRepository
{
    /**
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findAll()
    {
        return $this->createQueryBuilder()
            ->field('deleted')->equals(null);
    }
    
    /**
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findAllActive()
    {
        return $this->findAll()
            ->field('isActive')->equals(true);
    }
    
}
