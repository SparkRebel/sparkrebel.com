<?php

namespace PW\ActivityBundle\Repository;

use Doctrine\ODM\MongoDB\Query\Builder;
use PW\ApplicationBundle\Repository\AbstractRepository;
use PW\ApplicationBundle\Query\Builder\ParameterBag;
use PW\UserBundle\Document\User;

class ActivityRepository extends AbstractRepository
{
    /**
     * @param Builder $qb
     * @param ParameterBag $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    protected function handleOptions(Builder $qb, ParameterBag $options = null)
    {
        if ($options->has('onlyNew')) {
            $qb->field('isNew')->equals(true);
        }

        return $qb;
    }


    /**
     * @param User $user
     * @param array $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByUser(User $user, array $options = array())
    {
        return $this->createQueryBuilderWithOptions($options)
            ->field('user')->references($user)
            ->field('category')->equals('user')
            ->sort('modified', 'desc')
            ->sort('created', 'desc');
    }

    /**
     * @param User $user
     * @param array $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByUsersFriends(User $user, array $options = array())
    {
        return $this->createQueryBuilderWithOptions($options)
            ->field('user')->references($user)
            ->field('category')->equals('friend')
            ->sort('created', 'desc');
    }
}
