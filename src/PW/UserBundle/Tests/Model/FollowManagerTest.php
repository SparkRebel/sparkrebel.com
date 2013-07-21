<?php

namespace PW\UserBundle\Tests\Model;

use PW\ApplicationBundle\Tests\AbstractTest;

class FollowManagerTest extends AbstractTest
{
    /**
     * @var \PW\UserBundle\Model\UserManager
     */
    protected $userManager;

    /**
     * @var \PW\BoardBundle\Model\BoardManager
     */
    protected $boardManager;

    /**
     * @var \PW\UserBundle\Model\FollowManager
     */
    protected $followManager;

    protected $_fixtures = array(
        'PW\UserBundle\DataFixtures\MongoDB\TestUsers',
        'PW\CategoryBundle\DataFixtures\MongoDB\TestCategories',
        'PW\BoardBundle\DataFixtures\MongoDB\TestBoards',
        'PW\UserBundle\DataFixtures\MongoDB\TestUserFollows',
        'PW\UserBundle\DataFixtures\MongoDB\TestBoardFollows',
    );

    public function setUp()
    {
        parent::setUp();
        $this->userManager   = $this->container->get('pw_user.user_manager');
        $this->boardManager  = $this->container->get('pw_board.board_manager');
        $this->followManager = $this->container->get('pw_user.follow_manager');
    }

    /**
     * Test one User following another User
     */
    public function testFollowUser()
    {
        /* @var $user1 \PW\UserBundle\Document\User */
        $user1 = $this->userManager->getRepository()->findOneByName("User #1");

        /* @var $user2 \PW\UserBundle\Document\User */
        $user2 = $this->userManager->getRepository()->findOneByName("User #3");

        $follow = $this->followManager->addFollower($user1, $user2);
        $this->followManager->update($follow);

        $user1Follow = $this->followManager->isFollowing($user1, $user2);
        $this->assertNotEmpty($user1Follow);

        $user2Follow = $this->followManager->isFollowing($user2, $user1);
        $this->assertEmpty($user2Follow);
    }

    /**
     * Test one User unfollowing another User
     */
    public function testUnfollowUser()
    {
        /* @var $user1 \PW\UserBundle\Document\User */
        $user1 = $this->userManager->getRepository()->findOneByName("User #1");

        /* @var $user2 \PW\UserBundle\Document\User */
        $user2 = $this->userManager->getRepository()->findOneByName("User #2");

        $this->followManager->removeFollower($user1, $user2);

        $user1Follow = $this->followManager->isFollowing($user1, $user2);
        $this->assertEmpty($user1Follow);

        $user2Follow = $this->followManager->isFollowing($user2, $user1);
        $this->assertEmpty($user2Follow);
    }

    /**
     * Test one User unfollowing another User who is also following them (friends)
     */
    public function testUnfollowReciprocalUser()
    {
        /* @var $user1 \PW\UserBundle\Document\User */
        $user1 = $this->userManager->getRepository()->findOneByName("User #1");

        /* @var $user2 \PW\UserBundle\Document\User */
        $user2 = $this->userManager->getRepository()->findOneByName("User #" . $GLOBALS['FIXTURE_USERS_TOTAL']);

        $this->followManager->removeFollower($user1, $user2);

        $user1Follow = $this->followManager->isFollowing($user1, $user2);
        $this->assertEmpty($user1Follow);

        $user2Follow = $this->followManager->isFollowing($user2, $user1);
        $this->assertNotEmpty($user2Follow);
    }

    /**
     * Test one User following a Board
     */
    public function testFollowBoard()
    {
        /* @var $user \PW\UserBundle\Document\User */
        $user = $this->userManager->getRepository()->findOneByName("User #1");

        /* @var $board \PW\BoardBundle\Document\Board */
        $board = $this->boardManager->getRepository()->findOneByName("User #2 - Board #2");

        $userFollow = $this->followManager->isFollowing($user, $board);
        $this->assertEmpty($userFollow, 'Unable to test adding follower to Board (User is already following the Board).');

        $follow = $this->followManager->addFollower($user, $board);
        $this->followManager->update($follow);

        $userFollow = $this->followManager->isFollowing($user, $board);
        $this->assertNotEmpty($userFollow, 'Failed adding follower to Board.');
    }

    /**
     * Test one User unfollowing a Board
     */
    public function testUnfollowBoard()
    {
        /* @var $user \PW\UserBundle\Document\User */
        $user = $this->userManager->getRepository()->findOneByName("User #1");

        /* @var $board \PW\BoardBundle\Document\Board */
        $board = $this->boardManager->getRepository()->findOneByName("User #2 - Board #1");

        $userFollow = $this->followManager->isFollowing($user, $board);
        $this->assertNotEmpty($userFollow, 'Unable to test removing follower from Board (User is not following the Board).');

        $follow = $this->followManager->removeFollower($user, $board);
        $this->followManager->update($follow);

        $userFollow = $this->followManager->isFollowing($user, $board);
        $this->assertEmpty($userFollow, 'Failed removing follower from Board.');
    }
}
