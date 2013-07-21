<?php

namespace PW\ApplicationBundle\Repository;

use Doctrine\ODM\MongoDB\Query\Builder;
use PW\ApplicationBundle\Query\Builder\ParameterBag;
use PW\UserBundle\Document\User;

class EmailRepository extends AbstractRepository
{
    /**
     * @param \PW\UserBundle\Document\User $user
     * @param string $type
     * @return \PW\ApplicationBundle\Document\Email
     */
    public function findByUserAndType(User $user, $type = null, array $options = array())
    {
        $qb = $this->createQueryBuilderWithOptions($options)
            ->field('user')->references($user)
            ->sort('created', 'desc');

        if (!empty($type)) {
            $qb->field('type')->equals($type);
        }

        return $qb;
    }
}