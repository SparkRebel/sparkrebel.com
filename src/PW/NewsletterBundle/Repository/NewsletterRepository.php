<?php

namespace PW\NewsletterBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class NewsletterRepository extends DocumentRepository
{
    /**
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findAllDesc($limit = null)
    {
        $qb = $this->createQueryBuilder()
            ->sort('created', 'desc');

        if (!empty($limit)) {
            $qb->limit((int) $limit);
        }

        return $qb;
    }

    /**
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findAllForSending()
    {
        $now = new \DateTime(null, new \DateTimeZone('America/New_York'));

        $qb = $this->createQueryBuilder()
            ->field('status')->equals('pending')
            ->field('sendAt')->lte(new \MongoDate($now->getTimestamp()));

        return $qb;
    }
}