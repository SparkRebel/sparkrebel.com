<?php

namespace PW\PostBundle\Model;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use PW\ApplicationBundle\Model\AbstractManager;
use PW\PostBundle\Document\Post;
use PW\AssetBundle\Document\Asset;
use PW\PostBundle\Model\PostActivityManager;
use PW\UserBundle\Document\User;
use Doctrine\Common\Collections\ArrayCollection;
use PW\PostBundle\Document\StreamItem;

/**
 * @method \PW\PostBundle\Repository\PostRepository getRepository()
 * @method \PW\PostBundle\Document\Post find() find(string $id)
 * @method \PW\PostBundle\Document\Post create() create(array $data)
 * @method void delete() delete(\PW\PostBundle\Document\Post $post, \PW\UserBundle\Document\User $deletedBy, bool $safe, bool $andFlush)
 */
class PostManager extends AbstractManager
{
    /**
     * @var \PW\PostBundle\Model\PostActivityManager
     */
    protected $postActivityManager;

    /*
     * redis max queue size
     */
    protected $maxQueueSize = 500;

    protected $maxScore = 2147483647;
    /**
     * @param string $id
     * @param User $createdBy
     * @return \PW\PostBundle\Document\Post
     * @throws NotFoundHttpException
     */
    public function createRepost($id, User $createdBy = null)
    {
        /* @var $original \PW\PostBundle\Document\Post */
        $original = $this->getRepository()->find($id);
        if (!$original) {
            throw new NotFoundHttpException('Original post not found');
        }
        $post = $this->create(array('createdBy' => $createdBy));
        $post->clonePost($original);
        return $post;
    }

    /**
     * @param Asset $asset
     * @param string $description
     * @param User $createdBy
     * @return \PW\PostBundle\Document\Post
     */
    public function createAssetPost(Asset $asset, $description = null, User $createdBy = null)
    {
        return $this->create(array(
            'createdBy' => $createdBy,
            'description' => $description,
            'target' => $asset,
            'image' => $asset,
            'link' => $asset->getSourcePage()
        ));
    }

    /**
     * @param Post $post
     */
    public function processCounts(Post $post)
    {
        if (!$post || $post->getDeleted()) {
            return;
        }

        $commentCount = $this->getPostActivityManager->getRepository()
            ->findByPost($post)
            ->count()->getQuery()->execute();

        $repostCount = $this->getRepository()
            ->findByParent($post)
            ->count()->getQuery()->execute();

        $aggregateRepostCount = $this->getRepository()
            ->findByOriginal($post)
            ->count()->getQuery()->execute();

        $post->setCommentCount($commentCount);
        $post->setRepostCount($repostCount);
        $post->setAggregateRepostCount($aggregateRepostCount);
        $this->save($post, array('validate' => false));
    }

    /**
     * Creates an array map of Boards => Categories for a User
     *
     * @param User $user
     * @return array
     */
    public function generateBoardCategoryMap(User $user)
    {
        $boardCategoryMap = array();
        $boards = $this->dm->getRepository('PWBoardBundle:Board')
            ->findByUser($user)
            ->getQuery()
            ->execute();

        foreach ($boards as $board /* @var $board \PW\BoardBundle\Document\Board */) {
            if ($board->getCategory() == null) {
                continue;
            }
            $boardCategoryMap[$board->getId()] = $board->getCategory()->getId();
        }

        return $boardCategoryMap;
    }

    /**
     * @param User $user
     * @param bool $repost
     * @return int
     */
    public function getPostCountForUser(User $user, $repost = false)
    {
        $qb = $this->getRepository()->findByUser($user);
        if ($repost) {
            $qb->field('parent')->exists(true);
        }

        return $qb->count()->getQuery()->execute();
    }

    /**
     * @return \PW\PostBundle\Model\PostActivityManager $postActivityManager
     */
    public function getPostActivityManager()
    {
        if (!($this->postActivityManager instanceOf PostActivityManager)) {
            $this->postActivityManager = $this->container->get('pw_post.post_activity_manager');
        }

        return $this->postActivityManager;
    }

    /**
     * Return the array of User instances, which resparked post given in param
     *
     * @param Post $post
     * @param User $user
     * @return ArrayCollection
     */
    public function getFriendsWhoResparkedPostForUser(Post $post, User $user)
    {
        $friends = $this->dm->getRepository('PWUserBundle:Follow')
          ->createQueryBuilder()
          ->field('target')->prime(true)
          ->field('isFriend')->equals(true)
          ->field('target.$ref')->equals('users')
          ->field('follower')->references($user)
          ->getQuery()->execute();

        $followsIds = array();
        foreach ($friends as $follows) {
            $target_id = $follows->getTarget()->getId();
            if(!in_array($target_id, $followsIds))
              array_push($followsIds, new \MongoId($follows->getTarget()->getId()));
        }

        $friends_who_resparked = new ArrayCollection();
        $resparked_posts = $this->getRepository()
            ->createQueryBuilder()
            ->field('createdBy')->prime(true)
            ->field('original')->references($post)
            ->field('createdBy.$id')->in($followsIds)
            ->getQuery()->execute();

        // check uniqness
        foreach($resparked_posts as $repost) {
          $user = $repost->getCreatedBy();
          if($friends_who_resparked->contains($user) === false && $repost->getCreatedBy() !== $post->getCreatedBy())
            $friends_who_resparked->add($user);
        }
        return $friends_who_resparked;
    }


    /**
     * Updating stream for unified stream
     *     
     **/
    public function updateStream($post)  
    {
        $good_to_process = true;
        if (!in_array($post->getPostType(), array('celeb', 'brand'))) {
            $good_to_process = false;
        } 
        if ($post->getParent() && in_array($post->getOriginal()->getPostType(), array('celeb', 'brand'))) {
            $good_to_process = true;
        }

        if (!$good_to_process) {
            return -1;
        }


        $to_all = false;
        $board = $post->getBoard();

        
        $this->dm->refresh($post);                
        if ($post->getParent() && $post->getOriginal()->getAggregateRepostCount() === 1) { //if its repost, add to each of brand|celeb followers stream 
            $to_all = true;
            $board = $post->getOriginal()->getBoard();
        }


        $brandRate = 0.02;
        $celebRate = 0.02;
        $useRate = $post->getPostType() === 'celeb' ? $celebRate : $brandRate;
        $followers = $this->getContainer()->get('pw_user.follow_manager')->getRepository()->findFollowersByBoard(
            $board
        )->getQuery()->execute();

        $followers = iterator_to_array($followers);
        
        if(count($followers) === 0) {
            return 0;
        }
        $weight = 1;    
        $created = $post->getCreated();
        if ($created) {
            $created = $created->getTimestamp();
        }
        $score = str_pad($this->maxScore - ($created  * $weight), strlen($this->maxScore), '0', STR_PAD_LEFT);
        
        $total = (int)round(count($followers) * $useRate);                
        if ($total === 0) { // for small number of followers always take one at least
            $total = 1;  
        }

        shuffle($followers);        
        if ($to_all === true) {
            $rand = $followers;
        } else {
            $rand = array_slice($followers, 0, $total);      
        }
        
        
        
        $redis = $this->getContainer()->get('snc_redis.default');
        foreach ($rand as $user) {
    
            if ((String)$user->getId() === (String)$post->getCreatedBy()->getId()) {
                continue;
            }                      
            $si = new StreamItem;
            $si
              ->setUser($user)
              ->setPost($post)
              ->setType('user')
              ->setScore($score)
            ;
            $this->dm->persist($si);
            $this->dm->flush(null, array('safe' => false, 'fsync' => false));

            $key = 'stream:{' . $user->getFollower()->getId() . '}:user';
            $redis->zadd($key, $score, $post->getId());            
            $count = $redis->zcard($key);
            
                        
        }
        return count($rand);        
    }
    

}
