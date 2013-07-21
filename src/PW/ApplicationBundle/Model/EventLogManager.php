<?php

namespace PW\ApplicationBundle\Model;

/**
 * @method \PW\ApplicationBundle\Repository\EventLogRepository getRepository()
 * @method \PW\ApplicationBundle\Document\Event find() find(string $id)
 * @method \PW\ApplicationBundle\Document\Event create() create(array $data)
 * @method void delete() delete(\PW\ApplicationBundle\Document\Event $event, \PW\UserBundle\Document\User $deletedBy, bool $safe, bool $andFlush)
 */
class EventLogManager extends AbstractManager
{
    /**
     * @var array
     */
    protected $flushOptions = array(
        'safe'  => false,
        'fsync' => false,
    );
}
