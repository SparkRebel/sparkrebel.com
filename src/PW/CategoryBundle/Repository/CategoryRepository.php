<?php

namespace PW\CategoryBundle\Repository;

use PW\ApplicationBundle\Repository\AbstractRepository;

class CategoryRepository extends AbstractRepository
{
    /**
     * @param string $type
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByType($type = null)
    {
        $qb = $this->createQueryBuilder()
            ->field('isActive')->equals(true)
            ->sort('created', 'asc');

        if (!empty($type)) {
            $qb->field('type')->equals($type);
        }

        return $qb;
    }

    public function findForElasticReindex($startTimestamp)
    {
        return $this->createQueryBuilder()
            ->field('isActive')->equals(true)
            ->field('created')->gte(new \MongoDate($startTimestamp));
    }
}
