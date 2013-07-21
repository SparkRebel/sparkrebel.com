<?php

namespace PW\ActivityBundle\Model;

use PW\ApplicationBundle\Model\AbstractManager;

/**
 * @method \PW\ActivityBundle\Repository\ActivityRepository getRepository()
 * @method \PW\ActivityBundle\Document\Activity find() find(string $id)
 * @method \PW\ActivityBundle\Document\Activity create() create(array $data)
 * @method void delete() delete(\PW\ActivityBundle\Document\Activity $activity, \PW\UserBundle\Document\User $deletedBy, bool $safe, bool $andFlush)
 */
class ActivityManager extends AbstractManager
{
    /**
     * @var array
     */
    protected $flushOptions = array(
        'safe'  => false,
        'fsync' => false,
    );
}
