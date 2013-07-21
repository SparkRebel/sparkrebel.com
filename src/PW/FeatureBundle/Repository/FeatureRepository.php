<?php

namespace PW\FeatureBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class FeatureRepository extends DocumentRepository
{
    /**
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findOneActiveBetweenDates($targetId, $start, $end)
    {
        $start = new \DateTime($start);
        $end = new \DateTime($end);

        $qb = $this->createQueryBuilder()
            ->field('isActive')->equals(true)
            ->field('target.$id')->equals(new \MongoId($targetId))
            ->field('start')->gte(new \MongoDate($start->getTimestamp()))
            ->field('end')->lte(new \MongoDate($end->getTimestamp()));

        return $qb;
    }
}
