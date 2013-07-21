<?php

namespace PW\InviteBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    PW\InviteBundle\Document\Code;

class CodeRepository extends DocumentRepository
{
    /**
     * @param string $status
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByStatus($status)
    {
        /* @var $qb \Doctrine\ODM\MongoDB\Query\Builder */
        $qb = $this->createQueryBuilder()
            ->field('assignedUser')->prime(true)
            ->sort('created', 'desc');

        switch (strtolower($status)) {
            case 'unused':
                $qb->field('usedCount')->equals(0);
                break;
            case 'exhausted':
                $qb->field('usesLeft')->equals(0);
                break;
            case 'active':
                $qb->addOr($qb->expr()->field('usedCount')->gt(0)->field('usesLeft')->gt(0));
                $qb->addOr($qb->expr()->field('maxUses')->equals(0));
                break;
        }

        return $qb;
    }
}
