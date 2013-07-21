<?php

namespace PW\UserBundle\Model;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;
use PW\UserBundle\Document\User;
use PW\UserBundle\Model\FollowManager;
use PW\AssetBundle\Provider\AssetProvider as AssetManager;

class UserManager extends BaseUserManager implements ContainerAwareInterface
{
    protected $dm = null;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \PW\UserBundle\Model\FollowManager
     */
    protected $followManager;

    /**
     * @var \PW\AssetBundle\Provider\AssetProvider
     */
    protected $assetManager;

    /**
     * @param array $data
     * @return \PW\UserBundle\Document\User
     */
    public function create(array $data = array())
    {
        /* @var $user \PW\UserBundle\Document\User */
        $user = parent::createUser();
        $user->fromArray($data);
        return $user;
    }

    /**
     * @param User $user
     * @param bool $andFlush
     * @return \PW\UserBundle\Document\User
     */
    public function update(User $user, $andFlush = true)
    {
        parent::updateUser($user, $andFlush);
        return $user;
    }

    /**
     * @param User $user
     * @param User $deletedBy
     * @param bool $safe
     */
    public function delete(User $user, User $deletedBy = null, $safe = true)
    {
        if ($safe) {
            $user->setDeleted(new \DateTime());
            if ($deletedBy !== null) {
                $user->setDeletedBy($deletedBy);
            }
        } else {
            return parent::deleteUser($user);
        }
        $this->getDocumentManager()->flush();
    }

    /**
     * @param User $user1
     * @param User $user2
     */
    public function makeFriends(User $user1, User $user2)
    {
        $followManager = $this->getFollowManager();
        $followManager->addFollower($user1, $user2, true);
        $followManager->addFollower($user2, $user1, true);
    }

    /**
     * @param User $user1
     * @param mixed $users
     */
    public function makeManyFriends(User $user1, $users)
    {
        foreach ($users as $user2) {
            $this->makeFriends($user1, $user2);
        }
    }

    /**
     * @param User $user
     * @return boolean|User
     */
    public function refreshFacebookIcon(User $user)
    {
        if (!$user->getFacebookId()) {
            return false;
        }

        $url = "https://graph.facebook.com/{$user->getFacebookId()}/picture?type=large";
        $asset = $this->getAssetManager()->addImageFromUrl($url, 'http://www.facebook.com', array(), true);

        if (!$asset) {
            return false;
        }

        $user->setIcon($asset);
        return $this->update($user);
    }

    /**
     * @param string $id
     * @param string $type
     * @return \PW\UserBundle\Document\User
     * @deprecated
     */
    public function find($id, $type = null)
    {
        if (empty($type)) {
            return $this->getRepository()->find($id);
        } else {
            return $this->getRepository()->findByType($type)
                ->field('_id')->equals($id)
                ->getQuery()->getSingleResult();
        }
    }

    /**
     * Loads a user by username
     *
     * It is strongly discouraged to call this method manually as it bypasses
     * all ACL checks.
     *
     * @param string $username
     * @return UserInterface
     */
    public function loadUserByUsername($username)
    {
        $user = $this->findUserByUsernameOrEmail($username);

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('No user with name "%s" was found.', $username));
        }

        return $user;
    }

    /**
     * @return \PW\UserBundle\Model\FollowManager $followManager
     */
    public function getFollowManager()
    {
        if (!($this->followManager instanceOf FollowManager)) {
            $this->followManager = $this->container->get('pw_user.follow_manager');
        }

        return $this->followManager;
    }

    /**
     * @return \PW\AssetBundle\Provider\AssetProvider $assetManager
     */
    public function getAssetManager()
    {
        if (!($this->assetManager instanceOf AssetManager)) {
            $this->assetManager = $this->container->get('pw.asset');
        }

        return $this->assetManager;
    }

    /**
     * @return \PW\UserBundle\Repository\UserRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * getViewDataCommon
     *
     * If followingCount is in the return array - this method has already been called - bail early
     *
     * @param array &$return the data to be passed to the view
     * @param array $params  stub
     */
    public function getViewDataCommon(&$return, $params = array())
    {
        if (array_key_exists('followingCount', $return)) {
            return;
        }

        $followRepo = $this->getDocumentManager()->getRepository('PWUserBundle:Follow');

        // TODO this is only used to display a count
        $return['followers'] = $followRepo
            ->createQueryBuilder()
            ->field('user')->prime(true)
            ->field('deleted')->equals(null)
            ->field('target')->references($return['user'])
            ->getQuery()->execute();

        $usersThatFollow = $brandsThatFollow = $vipsThatFollow = array();
        foreach ($return['followers'] as $follow) {
            $target = $follow->getFollower();
            $type = $target->getUserType();
            if ($type === 'brand' || $type === 'merchant') {
                $brandsThatFollow[] = $target;
            } elseif ($type === 'vip') {
                $vipsThatFollow[] = $target;
            } else {
                $usersThatFollow[] = $target;
            }
        }

        $return['usersThatFollow'] = $usersThatFollow;
        $return['brandsThatFollow'] = $brandsThatFollow;
        $return['vipsThatFollow'] = $vipsThatFollow;


        // TODO all these counts should be stored on the user record
        $followingUsers = $followingBrands = $followingVips = array();
        $following = $followRepo
            ->createQueryBuilder()
            ->field('target')->prime(true)
            ->field('target.$ref')->equals('users')
            ->field('follower')->references($return['user'])
            ->getQuery()->execute();

        $followingCount = 0;
        foreach ($following as $follow) {
            $target = $follow->getTarget();
            $type = $target->getUserType();
            if ($type === 'brand' || $type === 'merchant') {
                $followingBrands[] = $target;
            } elseif ($type === 'vip') {
                $followingVips[] = $target;
            } else {
                $followingUsers[] = $target;
            }
            $followingCount++;
        }
        $return['followingCount'] = $followingCount;

        $isMe        = false;
        $isFollowing = false;
        $me = $this->container->get('security.context')->getToken()->getUser();
        if ($me instanceof \PW\UserBundle\Document\User) {
            $isFollowing = $this->getFollowManager()->isFollowing($me, $return['user']);
            $isMe = ($return['user']->getId() === $me->getId());

            // additional session check
            if(
               $this->container->get('session')->has('followed_users/'.$return['user']->getId()) &&
               $this->container->get('session')->get('followed_users/'.$return['user']->getId()) === true
            ) {
                $isFollowing = true;
            } elseif(
                $this->container->get('session')->has('followed_users/'.$return['user']->getId()) &&
                $this->container->get('session')->get('followed_users/'.$return['user']->getId()) === false
            ) {
                $isFollowing = false;
            }
        }

        $return['isMe'] = $isMe;
        $return['isFollowing'] = $isFollowing;

        $friends = $followRepo
            ->createQueryBuilder()
            ->field('target')->prime(true)
            ->field('isFriend')->equals(true)
            ->field('target.$ref')->equals('users')
            ->field('follower')->references($return['user'])
            ->getQuery()->execute();



        $mutual = new \Doctrine\Common\Collections\ArrayCollection();

        //im logged in and not on my profile, so get mutual friends
        //TODO: optimize
        if($isMe === false && is_object($me)) {
          $fw = $followRepo
                      ->createQueryBuilder()
                      ->field('target')->prime(true)
                      ->field('isFriend')->equals(true)
                      ->field('target.$ref')->equals('users')
                      ->field('follower')->references($me)
                      ->getQuery()->execute();

          foreach ($friends as $f) {
            foreach ($fw as $_fw) {
              if ($f->getTarget()->getId() == $_fw->getTarget()->getId()) {
                $mutual->add($_fw->getTarget());
              }
            }
          }
        }



        $return['friends'] = $friends;
        $return['mutualFriends'] = $mutual;

        $activities = $this->getDocumentManager()->createQueryBuilder('PWActivityBundle:Activity')
            ->field('user')->references($return['user'])
            ->sort('created', -1)
            ->hint(array('user.$id' => 1, 'created' => -1)) // point mongo to right index
            ->limit(30)
            ->getQuery()->execute();

        $activities_correct = array();
        foreach($activities as $ac) {
            //try {
                $ac->getTarget();
                $activities_correct[] = $ac;
            //} catch (\Exception $e) {

            //}
        }
        //var_dump($activities_correct); die();
        $return['activities'] = $activities;
    }

    /**
     * getViewDataMyBoards
     *
     * @param array &$return the data to be passed to the view
     * @param array $params  stub
     */
    public function getViewDataMyBoards(&$return, $params = array())
    {
        if ($return['user']->getType() !== 'user') {
            $boardManager = $this->container->get('pw_board.board_manager');
            $return['boards'] = $boardManager->getCategorized($return['user']);
            return;
        }
        
        if (!is_array($params)) {
            $params = array();
        }
        if (!isset($params['sort_order']) || !in_array($params['sort_order'],array('asc','desc'))) {
            $params['sort_order'] = 'asc';
        }
        if ($return['user']->getUsername() == $this->container->getParameter('pw.system_user.sparkrebel.username')) {
            // sort System user collections in descending order
            $params['sort_order'] = 'desc';
        }

        $boardRepo = $this->getDocumentManager()->getRepository('PWBoardBundle:Board');

        $return['boards'] = $boardRepo
            ->createQueryBuilder()
            ->field('images')->prime()
            ->field('createdBy')->references($return['user'])
            ->field('isActive')->equals(true)
            ->sort('created', $params['sort_order'])
            ->getQuery()->execute();
    }

    /**
     * getViewDataMySparks
     *
     * @param array &$return the data to be passed to the view
     * @param array $params  stub
     */
    public function getViewDataMySparks(&$return, $params = array())
    {
    }

    /**
     * getViewDataOnSale
     *
     * @param array &$return the data to be passed to the view
     * @param array $params  stub
     */
    public function getViewDataOnSale(&$return, $params = array())
    {
    }

    /**
     * getViewDataCollectionsIFollow
     *
     * @param array &$return the data to be passed to the view
     * @param array $params  stub
     */
    public function getViewDataCollectionsIFollow(&$return, $params = array())
    {
        $followRepo = $this->getDocumentManager()->getRepository('PWUserBundle:Follow');
        $pageSize = 20;

        $primer = function($dm, $className, $fieldName, $ids, $hints) {
            $repository = $dm->getRepository($className);
            $qb = $repository->createQueryBuilder()
                ->field('id')->in($ids)
                ->field('images')->prime(true);
            $query = $qb->getQuery();
            $query->execute()->toArray();
        };

        $qb = $followRepo
            ->createQueryBuilder()
            ->field('deleted')->equals(null)
            ->field('target')->prime($primer)
            ->field('follower')->references($return['user'])
            ->field('target.$ref')->equals('boards')
            ->sort('created', 'desc')
            ->limit($pageSize);
    
        if(isset($params['follow_type'])) {
            $celebuser = $this->getRepository()->findOneByName('Celebs');
            if ($params['follow_type'] == 'celeb') {
                $qb->field('user')->references($celebuser);
            } else { 
                $qb
                    ->field('user.type')->equals($params['follow_type'])
                    ->field('user.$id')->notEqual(new \MongoId($celebuser->getId()));
            }
        }

        if (isset($params['page'])) {
            $qb->skip($params['page'] * $pageSize);
        }

        $qb->hint(array('follower.$id' => 1,'target.$id' => 1)); // point mongo to right index
        $return['followingBoards'] = $qb->getQuery()
            ->execute();
    }

    /**
     * getViewDataPeopleIFollow
     *
     * The template used for people I follow requires viewCommon
     *
     * @param array &$return the data to be passed to the view
     * @param array $params  stub
     */
    public function getViewDataPeopleIFollow(&$return, $params = array())
    {
        $this->getViewDataCommon($return);

        $followRepo = $this->getDocumentManager()->getRepository('PWUserBundle:Follow');

        $primer = function($dm, $className, $fieldName, $ids, $hints) {
            $repository = $dm->getRepository($className);
            $qb = $repository->createQueryBuilder()
                ->field('id')->in($ids)
                ->field('icon')->prime(true);

            $query = $qb->getQuery();
            $query->execute()->toArray();
        };

        $followingUsers = $followingBrands = $followingVips = array();

        $following = $followRepo
            ->createQueryBuilder()
            ->field('isActive')->equals(true)
            ->field('target')->prime($primer)
            ->field('follower')->references($return['user'])
            ->field('target.$ref')->equals('users')
            ->sort('created', 'asc')
            ->getQuery()
            ->execute();

        foreach ($following as $follow) {
            $target = $follow->getTarget();
            $type = $target->getUserType();
            if ($type === 'brand' || $type === 'merchant') {
                $followingBrands[] = $target;
            } elseif ($type === 'vip') {
                $followingVips[] = $target;
            } else {
                $followingUsers[] = $target;
            }
        }

        $return['followingBrands'] = $followingBrands;
        $return['followingVips'] = $followingVips;
        $return['followingUsers'] = $followingUsers;
    }

    /**
     * @param User $user
     */
    public function processCounts(User $user)
    {
        if (!$user || $user->getDeleted()) {
            return;
        }

        /* @var $boardManager \PW\BoardBundle\Model\BoardManager */
        $boardManager = $this->container->get('pw_board.board_manager');

        /* @var $postManager \PW\PostBundle\Model\PostManager */
        $postManager = $this->container->get('pw_post.post_manager');

        $userCounts = $user->getCounts();
        $userCounts->setBoards($boardManager->getBoardCountForUser($user));
        $userCounts->setPosts($postManager->getPostCountForUser($user));
        $userCounts->setReposts($postManager->getPostCountForUser($user, true));
        $user->setCounts($userCounts);
        $this->update($user);
    }

    /**
     * Updates only boards counts, used in board listemer
     *
     * @param User $user
     * @return void
     */
    public function processBoardCounts(User $user)
    {
       if (!$user || $user->getDeleted()) {
            return;
        }

        /* @var $boardManager \PW\BoardBundle\Model\BoardManager */
        $boardManager = $this->container->get('pw_board.board_manager');
        $userCounts = $user->getCounts();
        $userCounts->setBoards($boardManager->getBoardCountForUser($user));
        $user->setCounts($userCounts);
        $this->update($user);
    }

    /**
     * @return \PW\UserBundle\Mailer\Mailer
     */
    public function getMailer()
    {
        return $this->container->get('fos_user.mailer');
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Get ids of friends: Todo: Optimize and remove hydration
     *
     * @param User $user
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFriendsIdsForUser(User $user)
    {

        $friends = $this->getDocumentManager()->getRepository('PWUserBundle:Follow')
                            ->createQueryBuilder()
                            ->field('target')->prime(true)
                            ->field('isFriend')->equals(true)
                            ->field('target.$ref')->equals('users')
                            ->field('follower')->references($user)
                            ->getQuery()->execute();

        $arr = new \Doctrine\Common\Collections\ArrayCollection;
        foreach ($friends as $f) {
            $arr->add($f->getTarget()->getId());
        }
        return $arr;
    }

    public function getDocumentManager()
    {
        if($this->dm === null) {
            $this->dm = $this->container->get('doctrine_mongodb.odm.document_manager');
        }
        return $this->dm;

    }
}
