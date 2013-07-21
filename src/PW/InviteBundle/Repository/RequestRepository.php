<?php

namespace PW\InviteBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    PW\UserBundle\Document\User,
    PW\InviteBundle\Document\Code,
    PW\InviteBundle\Document\Request;

class RequestRepository extends DocumentRepository
{
    /**
     * @param string $status
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByStatus($status, $limit = null)
    {
        $qb = $this->createQueryBuilder()
            ->sort('created', 'desc');

        // This should have been a status field
        switch (strtolower($status)) {
            case 'pending':
                $qb->field('code')->exists(false);
                break;
            case 'assigned':
                $qb->field('code')->exists(true)
                   ->field('user')->exists(false);
                break;
            case 'registered':
                $qb->field('code')->exists(true)
                   ->field('user')->exists(true);
                break;
        }

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
        return $this->createQueryBuilder()
            ->field('createdBy')->references($user)
            ->sort('created', 'asc');
    }

    /**
     * @param array $ids
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByIds(array $ids)
    {
        foreach ($ids as $i => $id) {
            if (is_string($ids[$i])) {
                $ids[$i] = new \MongoId($ids[$i]);
            }
        }
        return $this->createQueryBuilder()
            ->field('id')->in($ids);
    }
}
