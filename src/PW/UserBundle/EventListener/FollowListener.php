<?php

namespace PW\UserBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware,
    Doctrine\ODM\MongoDB\Event\LifecycleEventArgs,
    Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs,
    PW\UserBundle\Document\Follow,
    PW\UserBundle\Document\User,
    PW\BoardBundle\Document\Board;

class FollowListener extends ContainerAware
{
    /**
     * @var \PW\UserBundle\Model\FollowManager
     */
    protected $followManager;

    /**
     * @var \PW\BoardBundle\Model\BoardManager
     */
    protected $boardManager;

    /**
     * @var \PW\ApplicationBundle\Model\EventManager
     */
    protected $eventManager;

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $follow = $args->getDocument();

        if ($follow instanceOf Follow) {
            $follower = $follow->getFollower();
            $target   = $follow->getTarget();

            //
            // User
            if ($target instanceOf User) {
                // Is target following follower?
                $inverse = $this->getFollowManager()->isFollowing($target, $follower);
                if ($inverse) {
                    $follow->setIsFriend(true);
                }
            }

            //
            // Board
            if ($target instanceOf Board) {
                $target->incFollowerCount();
            }
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $follow = $args->getDocument();

        if ($follow instanceOf Follow) {
            $follower = $follow->getFollower();
            $target   = $follow->getTarget();

            //
            // User
            if ($target instanceOf User) {
                if ($follow->getIsFriend()) {
                    // Is target following follower?
                    $inverse = $this->getFollowManager()->isFollowing($target, $follower);
                    if ($inverse && !$inverse->getIsFriend()) {
                        $inverse->setIsFriend(true);
                        $this->getFollowManager()->update($inverse);
                    }
                }
            }
        }
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $follow = $args->getDocument();

        if ($follow instanceOf Follow) {
            //
            // Deleted
            if ($args->hasChangedField('deleted')) {
                $deleted = $args->getNewValue('deleted');
                if (!empty($deleted)) {
                    $target = $follow->getTarget();

                    //
                    // User
                    if ($target instanceOf User) {
                        if ($follow->getIsFriend()) {
                            $follow->setIsFriend(false);
                        }
                    }

                    //
                    // Board
                    if ($target instanceOf Board) {
                        $target->decFollowerCount();
                    }
                }
            }

            // We are doing a update, so we must force Doctrine to update the
            // changeset in case we changed something above
            $dm   = $args->getDocumentManager();
            $uow  = $dm->getUnitOfWork();
            $meta = $dm->getClassMetadata(get_class($follow));
            $uow->recomputeSingleDocumentChangeSet($meta, $follow);
        }
    }

    /**
     * Emit any events to the Event manager after a successful update.
     *
     * @param LifecycleEventArgs $args dunno
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $follow = $args->getDocument();

        if ($follow instanceOf Follow) {
            //
            // Deleted
            if ($follow->getDeleted()) {
                $follower = $follow->getFollower();
                $target   = $follow->getTarget();

                //
                // User
                if ($target instanceOf User) {
                    // Is target following follower?
                    $inverse = $this->getFollowManager()->isFollowing($target, $follower);
                    if ($inverse && $inverse->getIsFriend()) {
                        $inverse->setIsFriend(false);
                        $this->getFollowManager()->update($inverse);
                    }

                    // Find all instances where $follower follows a board by $target
                    $boardsFollowers = $this->getFollowManager()->getRepository()
                        ->findByFollowerAndUser($follower, $target)
                        ->eagerCursor(true)
                        ->getQuery()->execute();

                    // Unfollow this User's Boards
                    foreach ($boardsFollowers as $boardFollower /* @var $boardFollower Follow */) {
                        $boardFollower->setNoEmit(true);
                        $this->getFollowManager()->delete($boardFollower, $follow->getDeletedBy(), true, false);
                    }
                    $this->getFollowManager()->flush();
                }
            }
        }
    }


    /**
     * Deleting all activities and notifications after removing follow
     *
     **/
    public function preRemove(LifecycleEventArgs $args)
    {
        $follow = $args->getDocument();
        $dm = $this->container->get('doctrine_mongodb.odm.document_manager');
        $activity_manager = $this->container->get('pw_activity.activity_manager');
        $notification_manager = $this->container->get('pw_activity.notification_manager');
        
        if ($follow instanceOf Follow) {
            $activity_manager
                            ->getRepository()
                            ->createQueryBuilder()
                            ->remove()
                            //->field('target')->references($follow)
                            ->field('target.$id')->equals(new \MongoId($follow->getId()))
                            ->field('target.$ref')->equals('follows')
                            ->getQuery()
                            ->execute();
            

            $notification_manager
                            ->getRepository()
                            ->createQueryBuilder()
                            ->remove()
                            //->field('target')->references($follow)
                            ->field('target.$id')->equals(new \MongoId($follow->getId()))
                            ->field('target.$ref')->equals('follows')
                            ->getQuery()
                            ->execute();      
            
            $target = $follow->getTarget();
            if ($target instanceof User) {
                // also remove follows for collections of target User
                $follower = $follow->getFollower();
                $this->getFollowManager()->getRepository()
                  ->createQueryBuilder()
                  ->remove()
                  ->field('follower')->references($follower)
                  ->field('user')->references($target)
                  ->field('target.$ref')->equals('boards')
                  ->getQuery()
                  ->execute();
            }
        }     
    }

    /**
     * @return \PW\UserBundle\Model\FollowManager
     */
    public function getFollowManager()
    {
        if ($this->followManager == null) {
            $this->followManager = $this->container->get('pw_user.follow_manager');
        }
        return $this->followManager;
    }

    /**
     * @return \PW\BoardBundle\Model\BoardManager
     */
    public function getBoardManager()
    {
        if ($this->boardManager == null) {
            $this->boardManager = $this->container->get('pw_board.board_manager');
        }
        return $this->boardManager;
    }
}
