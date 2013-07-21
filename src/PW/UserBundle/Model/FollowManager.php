<?php

namespace PW\UserBundle\Model;

use PW\ApplicationBundle\Model\AbstractManager;
use PW\UserBundle\Document\User;
use PW\BoardBundle\Document\Board;

/**
 * @method \PW\UserBundle\Repository\FollowRepository getRepository()
 * @method \PW\UserBundle\Document\Follow create() create(array $data)
 */
class FollowManager extends AbstractManager
{
    /**
     * @var array
     */
    protected $flushOptions = array(
        'safe'  => false,
        'fsync' => false,
    );

    /**
     * @param User $user
     * @param User|Board $target
     * @param bool $asFriends
     * @return \PW\UserBundle\Document\Follow
     */
    public function addFollower(User $user, $target, $asFriends = null)
    {
        if (!is_object($target)) {
            throw new \Exception('Expected User or Board object, got: ' . gettype($target));
        }

        $follow = $this->getRepository()->createQueryBuilder()
            ->field('follower')->references($user)
            ->field('target')->references($target)
            ->getQuery()->getSingleResult();

        if ($follow) {
            $follow->setIsActive(true);
            $follow->setDeleted(null);
            $follow->setDeletedBy(null);
        } else {
            $follow = $this->create();
            $follow->setFollower($user);
            $follow->setFollowing($target);
        }        

        if ($target instanceOf User && $asFriends === true) {
            $follow->setIsFriend(true);
        }

        if ($target instanceOf Board && $target->getCreatedBy() &&  $target->getCreatedBy()->isCeleb() === true) {
            $follow->setIsCeleb(true);
        }

        $this->dm->persist($follow);
        $this->dm->flush($follow, array('safe' => false, 'fsync' => false));
        return $follow;
    }

    /**
     * @param User $user
     * @param User|Board $target
     * @return \PW\UserBundle\Document\Follow
     */
    public function removeFollower(User $user, $target, $flush = true)
    {                   
        if ($follow = $this->isFollowing($user, $target)) {
            // delete follow for target 
            $this->delete($follow, $user, false, $flush);
            return $follow;
        }
        return false;
    }

    /**
     * @param User $user
     * @param mixed $target
     * @return \PW\UserBundle\Document\Follow
     */
    public function isFollowing(User $user, $target)
    {
        return $this->getRepository()
            ->findByFollowerAndTarget($user, $target)
            ->getQuery()->getSingleResult();
    }

    /**
     * @param \PW\UserBundle\Document\User $user1
     * @param \PW\UserBundle\Document\User $user1
     * @return mixed
     */
    public function getMutualFriends(User $user1, User $user2)
    {
        $user1Friends = $this->getRepository()
            ->findFriendsByUser($user1)
            ->getQuery()->execute();

        $user1FriendIds = array();
        foreach ($user1Friends as $user1Friend) {
            $user1FriendIds[] = new \MongoId($user1Friend->getTarget()->getId());
        }
       if (empty($user1FriendIds)) {
            return null;
        }

        return $this->getRepository()
            ->findFriendsByUser($user2)
            ->field('follower.$id')->in($user1FriendIds)
            ->getQuery()->execute();
    }


    /**
     * Clears all celebs follows
     * @param \PW\UserBundle\Document\User $user  
     * @param mixed array|null $exect ids
     * TODO: optimize   
     **/    
    public function clearCelebsFollowsForUser(User $user, $except = null)
    {
        $follows = $this->dm->getRepository('PWUserBundle:Follow')
                            ->createQueryBuilder()
                            ->field('target')->prime(true)
                            ->field('isCeleb')->equals(true)
                            ->field('deleted')->equals(null)
                            ->field('follower')->references($user)
                            ->getQuery()->execute();

        foreach ($follows as $follow) {            
            if((is_array($except) && !in_array($follow->getTarget()->getId(), $except)) || $except === null)
                $this->removeFollower($user, $follow->getTarget());
        }                        

    }

}
