<?php

namespace PW\PostBundle\Repository;

use Doctrine\ODM\MongoDB\Query\Builder,
    PW\ApplicationBundle\Repository\AbstractRepository,
    PW\ApplicationBundle\Query\Builder\ParameterBag,
    PW\PostBundle\Document\Post,
    PW\UserBundle\Document\User;

class PostActivityRepository extends AbstractRepository
{
    /**
     * @param Builder $qb
     * @param ParameterBag $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    protected function handleOptions(Builder $qb, ParameterBag $options = null)
    {
        if (!$options->has('includeDeleted')) {
            //$qb->field('isActive')->equals(true);
            $qb->field('deleted')->equals(null);
            
        }

        return $qb;
    }

    /**
     * @param \PW\PostBundle\Document\Post $post
     * @param array $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByPost(Post $post, array $options = array())
    {
        return $this->createQueryBuilderWithOptions($options)
            ->field('post')->references($post)
            ->sort('created', 'desc');
    }
}
