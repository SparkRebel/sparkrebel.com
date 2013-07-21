<?php

namespace PW\BoardBundle\Repository;

use Doctrine\ODM\MongoDB\Query\Builder,
    PW\ApplicationBundle\Repository\AbstractRepository,
    PW\ApplicationBundle\Query\Builder\ParameterBag,
    PW\BoardBundle\Document\Board,
    PW\UserBundle\Document\User;

class BoardRepository extends AbstractRepository
{
    /**
     * @param Builder $qb
     * @param ParameterBag $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    protected function handleOptions(Builder $qb, ParameterBag $options = null)
    {
        if (!$options->has('includeDeleted')) {
            $qb->field('isActive')->equals(true);
        }

        return $qb;
    }

    /**
     * @param Board $board
     * @param array $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findDuplicates(Board $board, array $options = array())
    {
        return $this->createQueryBuilderWithOptions($options)
            ->field('name')->equals($board->getName())
            ->field('category')->references($board->getCategory())
            ->field('createdBy')->references($board->getCreatedBy())
            ->field('isActive')->equals(true)
            ->sort('created', 'desc');
    }

    /**
     * @param \PW\UserBundle\Document\User $user
     * @param array $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByUser(User $user, array $options = array())
    {
        return $this->createQueryBuilderWithOptions($options)
            ->field('createdBy')->references($user)
            ->sort('created', 'asc');
    }

    /**
     * @param string $status
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByStatus($status)
    {
        /* @var $qb \Doctrine\ODM\MongoDB\Query\Builder */
        $qb = $this->createQueryBuilder()
            ->sort('created', 'desc');

        switch (strtolower($status)) {
            case 'active':
                $qb->field('isActive')->equals(true);
                break;
            case 'inactive':
                $qb->field('isActive')->equals(false);
                break;
            case 'deleted':
                $qb
                    ->field('isActive')->equals(false) // this is fast
                    ->field('deleted')->exists(true); // this would be tested on the subset returend by ^
                break;
            case 'all':
            default:
        }

        return $qb;
    }
    
    public function countTotalActiveBoards()
    {
       return $this->createQueryBuilder()
           ->field('isActive')->equals(true)
           ->count()->getQuery()->execute();
    }

    public function findForElasticReindex($startTimestamp)
    {
        return $this->createQueryBuilder()
            ->field('isActive')->equals(true)
            ->field('created')->gte(new \MongoDate($startTimestamp));
    }
}
