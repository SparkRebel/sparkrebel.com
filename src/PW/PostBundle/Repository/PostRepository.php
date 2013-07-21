<?php

namespace PW\PostBundle\Repository;

use Doctrine\ODM\MongoDB\Query\Builder,
    PW\ApplicationBundle\Repository\AbstractRepository,
    PW\ApplicationBundle\Query\Builder\ParameterBag,
    PW\PostBundle\Document\Post,
    PW\BoardBundle\Document\Board,
    PW\UserBundle\Document\User;

class PostRepository extends AbstractRepository
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
     * latest posts
     *
     * Latest global posts is a simple query: find me the last 100 entries in the posts collection
     * ordered by date. However, we are also going to want to be able to find the next 100 entries
     * after that. The posts collection is going to be very active - you can't simply paginate it
     * for two simple reasons:
     *  1) .skip(big number) is seriously inefficient
     *  2) new entries are going to be created every second
     *
     * The latter point is particularly important. The following queries could return a significant
     * number of duplciate results if inbetween them more posts are created:
     *  db.posts.find().sort({created: -1}).limit(100)
     *  ...
     *  db.posts.find().sort({created: -1}).limit(100).skip(100)
     *
     * So, instead of that when we are paginating we will do the following:
     *  db.posts.find().sort({created: -1}).limit(100)
     *  ...
     *  db.posts.find({created: {$gt: lastCreatedValue}).sort({created: -1}).limit(100)
     *
     * This will mean that any results created in the same second as the previous last result will
     * be MISSING from the returned results - but that's a relatively tiny problem, which we can
     * account for in the future if actually necessary.
     *
     * @param mixed $date    Where to start - the created date of the last post returned
     * @param array $options
     *
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findLatest($date = null, $limit = null, array $options = array())
    {
        $qb = $this->createQueryBuilderWithOptions($options)
            ->sort('created', 'desc');

        if (!empty($date)) {
            if (is_numeric($date)) {
                $qb->field('created')->lt(new \MongoDate($date));
            } else {
                $qb->field('created')->lt($date);
            }
        }

        if (!empty($limit)) {
            $qb->limit((int) $limit);
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
            ->field('createdBy')->references($user)
            ->sort('created', 'desc');
    }

    /**
     * @param \PW\PostBundle\Document\Post $post
     * @param array $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByParent(Post $post, array $options = array())
    {
        return $this->createQueryBuilderWithOptions($options)
            ->field('parent')->references($post)
            ->sort('created', 'desc');
    }

    /**
     * @param \PW\PostBundle\Document\Post $post
     * @param array $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByOriginal(Post $post, array $options = array())
    {
        return $this->createQueryBuilderWithOptions($options)
            ->field('original')->references($post)
            ->sort('created', 'desc');
    }

    /**
     * @param \PW\BoardBundle\Document\Board $board
     * @param array $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByBoard(\PW\BoardBundle\Document\Board $board, array $options = array())
    {
        return $this->createQueryBuilderWithOptions($options)
            ->field('board')->references($board)
            ->sort('created', 'desc')
            ->hint(array('board.$id' => 1,'isActive' => 1, 'created' => -1)) // point mongo to right index
        ;
    }

    /**
     * @param mixed $target
     * @param array $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByTarget($target, array $options = array())
    {
        return $this->createQueryBuilderWithOptions($options)
            ->field('target')->references($target)
            ->field('isActive')->equals(true)
            ->sort('created', 'desc');
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
            case 'deleted':
                $qb->field('isActive')->equals(false);
                break;
            case 'all':
            default:
        }

        return $qb;
    }

    /**
     * @param int $newerThan unix timestamp
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByDate($newerThan)
    {
    	return $this->createQueryBuilder()
    	    ->field('created')->gt(new \MongoDate($newerThan))
            ->field('isActive')->equals(true);
    }
    
    public function countTotalActivePosts()
    {
       return $this->createQueryBuilder()
           ->field('isActive')->equals(true)
           ->count()->getQuery()->execute();
    }

    public function findCuratorSparksToByProcessed()
    {
        $ts = new \DateTime('-14 days');
        return $this->createQueryBuilder()
           ->field('created')->gte(new \MongoDate($ts->getTimestamp()))
           ->field('isCuratorPost')->equals(true)
           ->field('isCuratorPostAlreadyProcessed')->equals(false);
    }
    
    public function countCuratorSparksToByProcessed()
    {
        return $this->findCuratorSparksToByProcessed()
           ->count()->getQuery()->execute();
    }

    public function findBoardPostsIncludingDeleted(Board $board)
    {
          return $this->createQueryBuilder()            
                    ->field('board')->references($board);                                        

    }

    public function findRecentTrendingFollows($maxItems = 12, $maxDaysBefore = 14)
    {
        $results = $this->findByDate(time() - ($maxDaysBefore * 86400))
            ->field('userType')->equals('user')
            ->field('original')->equals(null)
            ->field('board.$id')->exists(true)
            ->field('isCeleb')->in(array(null, false))
            ->map('function() { emit(this.board.$id, 1); }')
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

    public function findForElasticReindex($startTimestamp)
    {
        return $this->createQueryBuilder()
            ->field('isActive')->equals(true)
            ->field('created')->gte(new \MongoDate($startTimestamp));
    }
}
