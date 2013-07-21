<?php

namespace PW\PostBundle\Tests\Document;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\PostBundle\Document\Post,
    PW\PostBundle\Document\PostComment,    
    PW\UserBundle\Document\User,
    PW\AssetBundle\Document\Asset;

/**
 * PostTest
 */
class PostTest extends AbstractTest
{
    /**
     * Test creating a new Post
     */
    public function testDefaults()
    {
        $doc = new Post();
        $this->assertTrue($doc->getIsActive());
    }

    /**
     * Tests that `original` is automatically set from `parent`
     *
     * @covers \PW\PostBundle\Document\Post::prePersist
     */
    public function testOriginalSetAutomatically()
    {
        $p100 = new Post();
        $p100->setCreatedBy(new User());
        $p100->setDescription('1.0.0');
        $this->_dm->persist($p100);
        $this->_dm->flush($p100);

        $this->assertNull($p100->getParent());
        $this->assertNull($p100->getOriginal());

        $p110 = new Post();
        $p110->setCreatedBy(new User());
        $p110->setDescription('1.1.0');
        $p110->setParent($p100);
        $this->_dm->persist($p110);
        $this->_dm->flush($p110);

        $this->assertSame($p100, $p110->getParent());
        $this->assertSame($p100, $p110->getOriginal());

        $p111 = new Post();
        $p111->setCreatedBy(new User());
        $p111->setDescription('1.1.1');
        $p111->setParent($p110);
        $this->_dm->persist($p111);
        $this->_dm->flush($p111);

        $this->assertSame($p110, $p111->getParent());
        $this->assertSame($p100, $p111->getOriginal());

        $p120 = new Post();
        $p120->setCreatedBy(new User());
        $p120->setDescription('1.2.0');
        $p120->setParent($p100);
        $this->_dm->persist($p120);
        $this->_dm->flush($p120);

        $this->assertSame($p100, $p120->getParent());
        $this->assertSame($p100, $p120->getOriginal());

        $p200 = new Post();
        $p200->setCreatedBy(new User());
        $p200->setDescription('2.0.0');
        $this->_dm->persist($p200);
        $this->_dm->flush($p200);

        $this->assertNull($p200->getParent());
        $this->assertNull($p200->getOriginal());
    }


    public function testClonePostType()
    {
        $p1 = new Post();
        $a = new Asset();
        $a->setUrl(mt_rand());        
        $p1->setImage($a);
        $p1->setPostType('celeb');
        $p2 = new Post();
        $p2->clonePost($p1);
        $this->assertEquals($p2->getPostType(), 'celeb');
    }

    /**
     * Test that a Post owner's PostCount gets incremented
     *
     * @covers \PW\PostBundle\Document\Post::prePersist
     */
    public function testUserPostCountIncremented()
    {
        $GLOBALS['FIXTURE_USERS_TOTAL']  = 1;
        $GLOBALS['FIXTURE_BOARDS_TOTAL'] = 1;
        $GLOBALS['FIXTURE_POSTS_TOTAL']  = 2;

        $this->_loadFixtures(array(
            'PW\UserBundle\DataFixtures\MongoDB\TestUsers',
            'PW\CategoryBundle\DataFixtures\MongoDB\TestCategories',
            'PW\BoardBundle\DataFixtures\MongoDB\TestBoards',
            'PW\PostBundle\DataFixtures\MongoDB\TestPosts',
        ), false);

        /* @var $user \PW\UserBundle\Document\User */
        $user = $this->_dm->getRepository('PWUserBundle:User')->findOneByName('User #1');
        $this->assertEquals(4, $user->getCounts()->getPosts());

        return $user;
    }

    /**
     * Test that a RePost owner's RePostCount gets incremented
     *
     * @depends testUserPostCountIncremented
     * @covers \PW\PostBundle\Document\Post::prePersist
     */
    public function testUserRePostCountIncremented(User $user)
    {
        $this->assertEquals(2, $user->getCounts()->getReposts());
    }
    
    public function testFilteringActivitiesWhichArentActive()
    {
        $post = new Post();
        $post->setCreatedBy(new User());
        $post->setDescription('post with comments marked as deleted');
        $post->setParent($post);
        
        $stub = new PostComment();
        $stub->setIsActive(false);
        
        $stub2 = new PostComment();
        $stub2->setIsActive(true);
               
        $post->addActivity($stub);
        $post->addActivity($stub2);
        $this->assertEquals(1, count($post->getActivity()), "getActivity return only active records");        
        $a1 = $post->getActivity();
        $this->assertTrue($a1->first()->getIsActive());
        
        return $post;
    }
    
    /**
      * Test that a RePost owner's RePostCount gets incremented
      *
      * @depends testFilteringActivitiesWhichArentActive      
      */
     public function testFilteringRecentActivitiesWhichArentActive(Post $post)
     {
       $recent = clone $post->getActivity();
       $post->setRecentActivity($recent);
       
       $this->assertEquals(1, count($post->getRecentActivity()), "getRecentActivity return only active records");
       $a1 = $post->getActivity();
       $this->assertTrue($a1->first()->getIsActive());
       
     }
    
    /**
     * Relates to 
     * 1) Creating the post via ActivityController:comment action
     *    which runs addActivity - we should think about wrapping the class, for non repetition
     * 2) Deleting comment by admin
     */
    public function testRemovingRelatedActivitiesWhenDeletingPostComment()
    {
     
      list($post, $comment) = $this->setUpBasicPostAndComments();
      
      
      $post->removeActivity($comment); 
            
      $this->_dm->remove($comment);      
      $this->_dm->persist($post);
      $this->_dm->flush();
      
      $post_refreshed = $this->_dm->getRepository('PWPostBundle:Post')->findOneById($post->getId());
      
            
      $this->assertEquals(0, count($post_refreshed->getActivity()), "removes comment activity after flush");
      $this->assertEquals(0, count($post_refreshed->getRecentActivity()), "removes comment recent activity after flush");
      $this->assertEquals(0, $post_refreshed->getCommentCount(), "decrements comment count");
      
      
      $cm = $this->_dm->getRepository('PWPostBundle:PostComment')->findOneByContent('a new comment for deletion');
      
      $this->assertNull($cm, 'comment removed from repository');
    }
    
    
    public function testRemovingRelatedActivitiesWhenDeletingPostCommentReply()
    {
      list($post, $comment) = $this->setUpBasicPostAndComments();
      
    }
    
    public function testSettingVideoPostIfRelatedAssetIsVideo()
    {
        $doc = new Asset;
        $doc->setSourceUrl('http://youtube.com?v=xxx');
        $doc->setSource('video_site');
        $doc->setHash(sha1('http://youtube.com?v=xxx'));
        $doc->setVideoCode('xxx');

        $user = $this->_dm->getRepository('PWUserBundle:User')->findOneByName('User #1');
        $this->_dm->persist($doc);
        $this->_dm->flush();
        
        $p = new Post();
        $p->setDescription('testing_video_post');
      
        $p->setImage($doc);
        $p->setCreatedBy($user);
        $p->setDescription('1.0.0');
        $this->_dm->persist($p);
        $this->_dm->flush($p);
        
        $post = $this->_dm->getRepository('PWPostBundle:Post')->findOneById($p->getId());

        $this->assertTrue($post->getIsVideoPost());
    }
    
    protected function setUpBasicPostAndComments() {
      $GLOBALS['FIXTURE_USERS_TOTAL']  = 2;
      $GLOBALS['FIXTURE_BOARDS_TOTAL'] = 1;      
      $GLOBALS['FIXTURE_POSTS_TOTAL']  = 2;

      $this->_loadFixtures(array(
        'PW\UserBundle\DataFixtures\MongoDB\TestUsers',
        'PW\PostBundle\DataFixtures\MongoDB\TestPosts',
        'PW\CategoryBundle\DataFixtures\MongoDB\TestCategories',          
        'PW\BoardBundle\DataFixtures\MongoDB\TestBoards',

        ), false);


      $post = new Post();
      $post->setCreatedBy(new User());
      $post->setDescription('deletion_test');
      $this->_dm->persist($post);

      $userRepo = $this->_dm->getRepository('PWUserBundle:User');
      $user = $this->_dm->getRepository('PWUserBundle:User')->findOneByName('User #1');

      $comment = new PostComment();
      $comment->setCreatedBy($user);
      $comment->setPost($post);
      $comment->setContent('a new comment for deletion');

      $post->addActivity($comment);
      $this->_dm->persist($post);

      $this->_dm->persist($comment);
      $this->_dm->flush();


      $this->assertEquals(1, count($post->getActivity()), "adds comment to activity");
      $this->assertEquals(1, count($post->getRecentActivity()), "adds comment to recent activity");
      $this->assertEquals(1, count($post->getCommentCount()), "increments comment count");
      
      return array($post, $comment);
    }
    
}
