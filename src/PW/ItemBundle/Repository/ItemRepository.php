<?php
namespace PW\ItemBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class ItemRepository extends DocumentRepository
{
    public function findForElasticReindex($startTimestamp)
    {
        return $this->createQueryBuilder()
            ->field('created')->gte(new \MongoDate($startTimestamp));
    }
}
