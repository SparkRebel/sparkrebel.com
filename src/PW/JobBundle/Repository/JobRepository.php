<?php

namespace PW\JobBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class JobRepository extends DocumentRepository
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
    
    /**
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findAllActiveAndRunning()
    {
        return $this->findAllActive()
            ->field('startDate')->lte(new \MongoDate())
            ->field('endDate')->gte(new \MongoDate());
    }
}
