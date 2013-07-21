<?php

namespace PW\UserBundle\Repository;

use Doctrine\ODM\MongoDB\Query\Builder,
    PW\ApplicationBundle\Repository\AbstractRepository,
    PW\ApplicationBundle\Query\Builder\ParameterBag,
    PW\BoardBundle\Document\Board,
    PW\UserBundle\Document\User;

class FollowRepository extends AbstractRepository
{
    /**
     * @param Builder $qb
     * @param ParameterBag $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    protected function handleOptions(Builder $qb, ParameterBag $options = null)
    {
        if (!$options->has('includeDeleted')) {
            //we dont store deleted in follow now        
        }

        if ($options->has('targetType')) {
            $qb->field('target.$ref')->equals($options->get('targetType'));
        }

        return $qb;
    }

    /**
     * @param User $user
     * @param string $type
     * @param array $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findFollowingByUser(User $user, $type = null, array $options = array())
    {
        $qb = $this->createQueryBuilderWithOptions($options)
            ->field('target')->prime(true)
            ->field('follower')->references($user);

        if (!empty($type)) {
            $qb->field('target.$ref')->equals($type);
        }

        return $qb;
    }

    /**
     * @param User $user
     * @param array $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findFriendsByUser(User $user, array $options = array())
    {
        return $this->findFollowingByUser($user, 'users', $options)
            ->field('isFriend')->equals(true);
    }

    /**
     * @param User|Board $target
     * @param array $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findFollowersByTarget($target, array $options = array())
    {
        return $this->createQueryBuilderWithOptions($options)
            ->field('target')->references($target);
    }

    /**
     * @param User $user
     * @param array $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findFollowersByUser(User $user, array $options = array())
    {
        return $this->findFollowersByTarget($user, $options);
    }

    /**
     * @param Board $board
     * @param array $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findFollowersByBoard(Board $board, array $options = array())
    {
        return $this->findFollowersByTarget($board, $options);
    }

    /**
     * @param User $follower
     * @param User|Board $target
     * @param array $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByFollowerAndTarget(User $follower, $target, array $options = array())
    {
        return $this->createQueryBuilderWithOptions($options)
            ->field('follower')->references($follower)
            ->field('target')->references($target);
    }

    /**
     * @param User $follower
     * @param User $user
     * @param string $type
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByFollowerAndUser(User $follower, User $user, $type = 'boards', array $options = array())
    {
        $qb = $this->createQueryBuilderWithOptions($options)
            ->field('follower')->references($follower)
            ->field('user')->references($user);

        if (!empty($type)) {
            $qb->field('target.$ref')->equals($type);
        }

        return $qb;
    }

    /**
     * @param int $newerThan unix timestamp
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByDate($newerThan, $options = array())
    {
    	return $this->createQueryBuilderWithOptions($options)
    	    ->field('created')->gt(new \MongoDate($newerThan));
    }
    
    
    /**
     * @param User $board
     * @param array $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findFollowingBoardsByUser(User $user, $options = array())
    {
        $qb = $this->createQueryBuilder()
                  ->field('deleted')->equals(null)
                  ->field('follower')->references($user)

        ;
       
        return $qb;      
    }


    public function getCelebsThatUserFollows(User $user)
    {
        $rs =  $this->createQueryBuilder()
                    ->field('deleted')->equals(null)
                    ->field('isActive')->equals(true)
                    ->field('target')->prime('true')
                    ->field('follower')->references($user)
                    ->field('target.$ref')->equals('boards')
                    ->sort('name', 'asc')
                    ->field('isCeleb')->equals(true)
                    ->getQuery()->execute();

        $a = new \Doctrine\Common\Collections\ArrayCollection($rs->toArray());
        return $a->map(function($e){ return $e->getTarget(); });            
    }

    public function findRecentBrandFollowers($maxItems = 6, $maxDaysBefore = 14)
    {
        $results = $this->findByDate(time() - ($maxDaysBefore * 86400))
            ->field('deleted')->equals(null)
            ->field('target.type')->equals('brand')
            ->field('target.$id')->exists(true)
            ->map('function() { emit(this.target.$id, 1); }')
            ->reduce('function(k, vals) {
                var sum = 0;

                for (var i in vals) {
                    sum += vals[i];
                }

                return sum;
            }')
            ->getQuery()->execute();

        $arr = array();
        foreach ($results as $r) {
            $arr[(string)$r['_id']] = $r['value'];
        }

        arsort($arr);

        return array_slice($arr, 0, $maxItems, true);
    }

    public function findRecentCelebsFollowers($maxItems = 6, $maxDaysBefore = 14)
    {
        $user = $this->getDocumentManager()->getRepository('PWUserBundle:User')->findOneByName('Celebs');

        $results = $this->findByDate(time() - ($maxDaysBefore * 86400))
            ->field('deleted')->equals(null)
            ->field('user')->references($user)
            ->field('target.$ref')->equals('boards')
            ->field('target.$id')->exists(true)
            ->map('function() { emit(this.target.$id, 1); }')
            ->reduce('function(k, vals) {
                var sum = 0;

                for (var i in vals) {
                    sum += vals[i];
                }

                return sum;
            }')
            ->getQuery()->execute();

        $arr = array();
        foreach ($results as $r) {
            $arr[(string)$r['_id']] = $r['value'];
        }

        arsort($arr);

        return array_slice($arr, 0, $maxItems, true);
    }
}
