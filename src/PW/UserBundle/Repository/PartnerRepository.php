<?php

namespace PW\UserBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    PW\UserBundle\Document\User;

class PartnerRepository extends DocumentRepository
{
    /**
     * @param string $status
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByStatus($status, $limit = null)
    {
        $qb = $this->createQueryBuilder()
            ->field('status')->equals(strtolower($status))
            ->sort('created', 'desc');

        if (!empty($limit)) {
            $qb->limit((int) $limit);
        }

        return $qb;
    }

    /**
     * @param \PW\UserBundle\Document\User $user
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByUser(User $user)
    {
        $qb = $this->createQueryBuilder()
            ->field('user')->references($user);

        return $qb;
    }

    /**
     * @param \PW\UserBundle\Document\User $user
     * @return int
     */
    public function getTotalByUser(User $user)
    {
        return $this->findByUser($user)
            ->count()
            ->getQuery()
            ->execute();
    }
}
