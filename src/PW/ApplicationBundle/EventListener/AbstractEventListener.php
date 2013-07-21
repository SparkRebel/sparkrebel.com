<?php

namespace PW\ApplicationBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Doctrine\ODM\MongoDB\SoftDelete\Event\LifecycleEventArgs as SoftDeleteEventArgs;

abstract class AbstractEventListener extends ContainerAware
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var \PW\UserBundle\Model\UserManager
     */
    protected $userManager;

    /**
     * @var \PW\UserBundle\Model\FollowManager
     */
    protected $followManager;

    /**
     * @var \PW\PostBundle\Model\PostManager
     */
    protected $postManager;

    /**
     * @var \PW\BoardBundle\Model\BoardManager
     */
    protected $boardManager;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * The prePersist event occurs for a given document before the respective
     * DocumentManager persist operation for that document is executed.
     *
     * @param \Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
    }

    /**
     * The postPersist event occurs for an document after the document has been made persistent.
     * It will be invoked after the database insert operations.
     * Generated primary key values are available in the postPersist event.
     *
     * @param \Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
    }

    /**
     * The preUpdate event occurs before the database update operations to document data.
     *
     * @param \Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
    }

    /**
     * The postUpdate event occurs after the database update operations to document data.
     *
     * @param \Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
    }

    /**
     * @param \Doctrine\ODM\MongoDB\SoftDelete\Event\LifecycleEventArgs $args
     */
    public function preSoftDelete(SoftDeleteEventArgs $args)
    {
    }

    /**
     * @param \Doctrine\ODM\MongoDB\SoftDelete\Event\LifecycleEventArgs $args
     */
    public function postSoftDelete(SoftDeleteEventArgs $args)
    {
    }

    /**
     * @param \Doctrine\ODM\MongoDB\SoftDelete\Event\LifecycleEventArgs $args
     */
    public function preRestore(SoftDeleteEventArgs $args)
    {
    }

    /**
     * @param \Doctrine\ODM\MongoDB\SoftDelete\Event\LifecycleEventArgs $args
     */
    public function postRestore(SoftDeleteEventArgs $args)
    {
    }

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcher
     */
    public function getEventDispatcher()
    {
        if ($this->dispatcher === null) {
            $this->dispatcher = $this->container->get('event_dispatcher');
        }

        return $this->dispatcher;
    }

    /**
     * @return \PW\UserBundle\Model\UserManager
     */
    public function getUserManager()
    {
        if ($this->userManager === null) {
            $this->userManager = $this->container->get('pw_user.user_manager');
        }

        return $this->userManager;
    }

    /**
     * @return \PW\UserBundle\Model\FollowManager
     */
    public function getFollowManager()
    {
        if ($this->followManager === null) {
            $this->followManager = $this->container->get('pw_user.follow_manager');
        }

        return $this->followManager;
    }

    /**
     * @return \PW\PostBundle\Model\PostManager
     */
    public function getPostManager()
    {
        if ($this->postManager === null) {
            $this->postManager = $this->container->get('pw_post.post_manager');
        }

        return $this->postManager;
    }

    /**
     * @return \PW\BoardBundle\Model\BoardManager
     */
    public function getBoardManager()
    {
        if ($this->boardManager === null) {
            $this->boardManager = $this->container->get('pw_board.board_manager');
        }

        return $this->boardManager;
    }
}

