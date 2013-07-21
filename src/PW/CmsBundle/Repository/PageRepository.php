<?php

namespace PW\CmsBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    PW\CmsBundle\Document\Page;

class PageRepository extends DocumentRepository
{
    /**
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findAllActive()
    {
        return $this->createQueryBuilder()
            ->field('isActive')->equals(true)
            ->sort('created', 'desc');
    }

    /**
     * @param string $section
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findBySection($section)
    {
        return $this->createQueryBuilder()
            ->field('isActive')->equals(true)
            ->field('section')->equals($section)
            ->sort('created', 'desc');
    }

    /**
     * @param string $status
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByStatus($status, $limit = null)
    {
        $qb = $this->createQueryBuilder()
            ->sort('created', 'desc');

        switch (strtolower($status)) {
            case 'active':
                $qb->field('isActive')->equals(true);
                break;
            case 'inactive':
                $qb->field('isActive')->equals(false);
                break;
            case 'all':
            default:
                break;
        }

        if (!empty($limit)) {
            $qb->limit((int) $limit);
        }

        return $qb;
    }
}
