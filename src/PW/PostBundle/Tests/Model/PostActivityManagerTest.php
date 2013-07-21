<?php

namespace PW\PostBundle\Tests\Model;

use PW\ApplicationBundle\Tests\AbstractTest;

/**
 * PostManagerTest
 */
class PostActivityManagerTest extends AbstractTest
{

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
        $this->postActivityManager = $this->container->get('pw_post.post_activity_manager');
    }
        
    
    /**
     * testDelete
     *
     * It should also set the isActive property to false
     */
    public function testDelete()
    {
        /* @var $activity \PW\PostBundle\Document\PostActivity */
        $activity = $this->postActivityManager->getRepository()->findOneByContent("User #1 - Board #1 - Post #1 - Comment");
        $this->postActivityManager->delete($activity);

        /* @var $activity \PW\PostBundle\Document\PostActivity */
        $activity = $this->postActivityManager->getRepository()->findOneByContent("User #1 - Board #1 - Post #1 - Comment");
        $this->assertFalse($activity->getIsActive());
    }
}
