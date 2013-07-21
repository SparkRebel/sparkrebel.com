<?php

namespace PW\ApplicationBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    Doctrine\ODM\MongoDB\Query\Builder,
    PW\ApplicationBundle\Query\Builder\ParameterBag;

abstract class AbstractRepository extends DocumentRepository
{
    /**
     * @param array $options
     * @return \PW\ApplicationBundle\Query\Builder\ParameterBag
     */
    public function getOptions(array $options = array())
    {
        return new ParameterBag($options);
    }

    /**
     * @param array $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function createQueryBuilderWithOptions($options = array())
    {
        if (!($options instanceOf ParameterBag)) {
            $options = $this->getOptions($options);
        }

        $qb = $this->createQueryBuilder();

        // Eager Cursor
        if ($options->has('eagerCursor') || $options->has('eager')) {
            $qb->eagerCursor(true);
        }

        // Prime References
        if ($options->has('prime')) {
            $prime = $options->get('prime', array());
            if (!is_array($prime)) {
                $prime = array($prime);
            }
            foreach ($prime as $field) {
                $qb->field($field)->prime(true);
            }
        }

        // Sort
        if ($options->has('sortBy') && $options->has('sortDir')) {
            $qb->sort($options->get('sortBy'), $options->get('sortDir'));
        }

        // Limit
        if ($options->has('limit')) {
            $qb->limit($options->get('limit'));
        }

        // Offset
        if ($options->has('offset')) {
            $qb->offset($options->get('offset'));
        }

        return $this->handleOptions($qb, $options);
    }

    /**
     * Handles options in subclasses
     *
     * @param Builder $qb
     * @param ParameterBag $options
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    protected function handleOptions(Builder $qb, ParameterBag $options = null)
    {
        return $qb;
    }
}
