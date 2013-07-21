<?php

namespace PW\BoardBundle\Tests\EventListener\Functional;

use PW\ApplicationBundle\Tests\AbstractTest;

/**
 * DeleteBoardTest
 *
 * @group board
 * @group frontend
 */
class DeleteBoardTest extends AbstractTest
{
    /**
     * @var \PW\BoardBundle\Model\BoardManager
     */
    protected $boardManager;

    /**
     * @var \PW\PostBundle\Model\PostManager
     */
    protected $postManager;

    /**
     * @var \PW\PostBundle\Model\PostActivityManager
     */
    protected $postActivityManager;

    /**
     * @var \PW\UserBundle\Model\FollowManager
     */
    protected $followManager;

    protected $_fixtures = array(
        'PW\UserBundle\DataFixtures\MongoDB\TestUsers',
        'PW\CategoryBundle\DataFixtures\MongoDB\TestCategories',
        'PW\BoardBundle\DataFixtures\MongoDB\TestBoards',
        'PW\PostBundle\DataFixtures\MongoDB\TestPosts',
        'PW\PostBundle\DataFixtures\MongoDB\TestPostActivities',
        'PW\UserBundle\DataFixtures\MongoDB\TestUserFollows',
        'PW\UserBundle\DataFixtures\MongoDB\TestBoardFollows',
    );

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->boardManager        = $this->container->get('pw_board.board_manager');
        $this->postManager         = $this->container->get('pw_post.post_manager');
        $this->postActivityManager = $this->container->get('pw_post.post_activity_manager');
        $this->followManager       = $this->container->get('pw_user.follow_manager');
    }

    /**
     * Test that deleting a board also deletes that boards's posts, followers, etc.
     */
    public function testDeletingBoardCascades()
    {
        // This is a test that cascading works. Cascading is excuted by the EventManager
        $this->container->get('pw.event')->setMode('foreground');

        /* @var $board \PW\BoardBundle\Document\Board */
        $board = $this->boardManager->getRepository()->findOneByName('User #1 - Board #1');
        $this->boardManager->delete($board);

        /* This event is not triggered directly by deleting a board. It is triggered by deleting a
           follow record. The important thing here is not whether the event is triggered but
           whether the follow records are deleted
        $this->assertEventTriggered('board.unfollow', array(
            'targetId' => $board->getId(),
        ));
        */

        $board = $this->boardManager->getRepository()->findOneByName('User #1 - Board #1');
        $this->assertFalse($board->getIsActive());

        $boardFollowers = $this->followManager->getRepository()
            ->findFollowersByBoard($board, array('includeDeleted' => true))
            ->getQuery()->execute();

        $this->assertGreaterThan(0, $boardFollowers->count());

        foreach ($boardFollowers as $follower) {
            $this->assertFalse($follower->getIsActive());
        }

        $boardPosts = $this->postManager->getRepository()
            ->findByBoard($board, array('includeDeleted' => true))
            ->getQuery()->execute();

        $this->assertGreaterThan(0, $boardPosts->count());

        foreach ($boardPosts as $post) {
            $this->assertFalse($post->getIsActive());
        }
    }
}
