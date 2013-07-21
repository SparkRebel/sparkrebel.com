<?php

namespace PW\UserBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * UserRepository
 */
class UserRepository extends DocumentRepository
{
    /**
     * findByType
     *
     * @param string $type   of user
     * @param string $status if specified, restricted to this status
     *
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByType($type, $status = null, $sort = true)
    {
        $qb = $this->createQueryBuilder()
            ->field('type')->equals($type);
        if ($sort) {
            $qb->sort('created', 'desc');
        }

        switch (strtolower($status)) {
            case 'justActive':
                $qb->field('isActive')->equals(true);
                break;
            case 'active':
                $qb->field('isActive')->equals(true);
                $qb->field('deleted')->equals(null);
                break;
            case 'inactive':
            case 'deleted':
                $qb->field('isActive')->equals(false);
                break;
            case 'all':
            default:
        }

        return $qb;
    }

    /**
     * findByIds
     *
     * TODO why does this method exist
     *
     * @param array $ids to return
     *
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByIds(array $ids)
    {
        return $this->createQueryBuilder()
            ->field('$id')->in($ids)
            ->sort('created', 'desc');
    }

    /**
     * @param array $facebookIds to return
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByFacebookIds(array $facebookIds)
    {
        return $this->createQueryBuilder()
            ->field('facebookId')->in($facebookIds);
    }
    
    public function findInternsWithBoards()
    {
        return $this->findWithRoles(array('ROLE_INTERN'));                   
    }

    public function findInternsAndCuratorsWithBoards()
    {
        return $this->findWithRoles(array('ROLE_INTERN', 'ROLE_CURATOR'));                    
    }
    
    public function findInternsAdminsAndCuratorsWithBoards()
    {
        return $this->findWithRoles(array('ROLE_INTERN', 'ROLE_CURATOR', 'ROLE_ADMIN'));        
    }

    public function countTotalActiveUsers()
    {
       return $this->createQueryBuilder()
           ->field('isActive')->equals(true)
           ->count()->getQuery()->execute();
    }

    protected function findWithRoles(array $roles)
    {
        $qb = $this->createQueryBuilder()
                    ->field('roles')->in($roles)
                    ->prime('created_at')
                    ->sort('name', 'asc');
                    
        return $qb->getQuery()->execute();       
    }

    public function findByTypeAndStartLetter($type, $status, $firstLetter)
    {
        $qb = $this->findByType($type, $status);
        $qb->field('name')->equals(new \MongoRegex('/^'.$firstLetter.'/i'));

        return $qb;
    }

    public function findForElasticReindex($startTimestamp)
    {
        return $this->createQueryBuilder()
            ->field('isActive')->equals(true)
            ->field('created')->gte(new \MongoDate($startTimestamp));
    }
}
