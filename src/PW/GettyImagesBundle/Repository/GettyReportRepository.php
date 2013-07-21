<?php

namespace PW\GettyImagesBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class GettyReportRepository extends DocumentRepository
{
    /**
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findAll()
    {
        return $this->createQueryBuilder()
            ->field('deleted')->equals(null);
    }  
}
