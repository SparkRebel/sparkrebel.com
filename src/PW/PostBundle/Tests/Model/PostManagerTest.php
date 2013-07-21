<?php

namespace PW\PostBundle\Tests\Model;

use PW\ApplicationBundle\Tests\AbstractTest;

/**
 * PostManagerTest
 */
class PostManagerTest extends AbstractTest
{
    /**
     * @var \PW\PostBundle\Model\PostManager
     */
    protected $postManager;

    /**
     * @var \PW\PostBundle\Model\PostActivityManager
     */
    protected $postActivityManager;

    protected $_fixtures = array(
        'PW\UserBundle\DataFixtures\MongoDB\TestUsers',
        'PW\CategoryBundle\DataFixtures\MongoDB\TestCategories',
        'PW\BoardBundle\DataFixtures\MongoDB\TestBoards',
        'PW\PostBundle\DataFixtures\MongoDB\TestPosts',
        'PW\PostBundle\DataFixtures\MongoDB\TestPostActivities',
    );

    /**
     * setUp
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->postManager         = $this->container->get('pw_post.post_manager');
        $this->postActivityManager = $this->container->get('pw_post.post_activity_manager');
    }

    /**
     * testDelete
     *
     * It should also set the isActive property to false
     */
    public function testDelete()
    {
        /* @var $post \PW\PostBundle\Document\Post */
        $post = $this->postManager->getRepository()->findOneByDescription("User #1 - Board #1 - Post #1");
        $this->postManager->delete($post);

        /* @var $post \PW\PostBundle\Document\Post */
        $post = $this->postManager->getRepository()->findOneByDescription("User #1 - Board #1 - Post #1");
        $this->assertFalse($post->getIsActive());        
    }
    
    //unifinished
    public function testDeletingSparkThatHasBeenResparked()
    {
      $this->container->setParameter('pw_event.mode', 'foreground');
      $post = $this->postManager->getRepository()->findOneByDescription("User #1 - Board #1 - Post #2");
      $image = new \PW\AssetBundle\Document\Asset;
      $post->setImage($image);
      
      $userManager = $this->container->get('pw_user.user_manager');
      $u1 = $userManager->getRepository()->findOneByEmail('testuser1@example.com');
      $u2 = $userManager->getRepository()->findOneByEmail('testuser2@example.com');
      
      $repost1 = $this->postManager->createRepost($post->getId(), $u1);      
      $repost2 = $this->postManager->createRepost($post->getId(), $u2);
      
      // adding reposts
      $this->_dm->persist($repost1);
      $this->_dm->persist($repost2);
      $this->_dm->flush(null, array('safe' => true, 'fsync' => true));
            
      $reposts = $this->postManager->getRepository()
          ->findByOriginal($post)
          ->getQuery()->execute();
      $this->assertEquals(3, count($reposts));    
      
      
      $this->postManager->delete($post); // deleting original post
      
      //check if original is deleted
      $post_refreshed = $this->postManager->getRepository()->find($post->getId());
      $this->assertFalse($post->getIsActive(), "original post isnt active");
      
    }
    
}
