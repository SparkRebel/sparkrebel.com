<?php

namespace PW\PostBundle\Model;

use PW\ApplicationBundle\Model\AbstractManager;

/**
 * @method \PW\PostBundle\Repository\PostActivityRepository getRepository()
 */
class PostActivityManager extends AbstractManager
{
    /**
     * @var array
     */
    protected $flushOptions = array(
        'safe'  => false,
        'fsync' => false,
    );
}
