<?php

namespace PW\UserBundle\EventListener;

use PW\ApplicationBundle\EventListener\AbstractEventListener;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\SoftDelete\Event\LifecycleEventArgs as SoftDeleteEventArgs;
use PW\UserBundle\Events as UserEvents;
use PW\UserBundle\Event\UserEvent;
use PW\UserBundle\Document\User;

class UserListener extends AbstractEventListener
{
    /**
     * The prePersist event occurs for a given document before the respective
     * DocumentManager persist operation for that document is executed.
     *
     * @param \Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if ($document instanceOf User) {
            /** @var \Symfony\Component\HttpFoundation\Session $session  */
            $session = $this->container->get('session');

            $utm = $session->get('utm_parameters', array());
            if (!empty($utm)) {
                $document->setUtmData($utm);
            }

            $subId = $session->get('sub_id', false);
            if (!empty($subId)) {
                $document->setSubId($subId);
            }
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $user = $args->getDocument();

        if ($user instanceOf User) {
            if ($user->getDeleted()) {
                $this->deleteFollows($user);
                $this->deleteBoards($user);
            }
        }
    }

    /**
     * Note that deleteing a board cascades to posts and followers
     *
     * If boards have _just_ been deleted they will be returned by the query, but the doctrine
     * proxy object will have isActive set to false. Double check that you are infact going to
     * delete something as otherwise a loop is created.
     *
     * @param mixed $user object
     */
    protected function deleteBoards($user)
    {
        /* @var $boardManager \PW\BoardBundle\Model\BoardManager */

        $boardManager = $this->container->get('pw_board.board_manager');
        $userBoards  = $boardManager->getRepository()
            ->findByUser($user)
            ->field('isActive')->equals(true)
            ->getQuery()->execute();

        if (!count($userBoards)) {
            return;
        }

        $doubleCheck = $userBoards->getNext();
        if (!$doubleCheck->getIsActive()) {
            return;
        }
        $userBoards->rewind();

        $boardManager->deleteAll($userBoards, $user->getDeletedBy());
    }

    /**
     * deleteFollows
     *
     * @param mixed $user object
     */
    protected function deleteFollows($user)
    {
        /* @var $followManager \PW\UserBundle\Model\FollowManager */
        $followManager = $this->container->get('pw_user.follow_manager');
        $userFollowers = $followManager->getRepository()
            ->findFollowersByUser($user)
            ->getQuery()->execute();

        if (!count($userFollowers)) {
            return;
        }

        $followManager->deleteAll($userFollowers, $user->getDeletedBy());
    }
}
