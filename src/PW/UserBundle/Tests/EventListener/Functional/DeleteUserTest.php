<?php

namespace PW\UserBundle\Tests\EventListener\Functional;

use PW\ApplicationBundle\Tests\AbstractTest;

class DeleteUserTest extends AbstractTest
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

    public function setUp()
    {
        parent::setUp();
        $this->userManager         = $this->container->get('pw_user.user_manager');
        $this->boardManager        = $this->container->get('pw_board.board_manager');
        $this->postManager         = $this->container->get('pw_post.post_manager');
        $this->postActivityManager = $this->container->get('pw_post.post_activity_manager');
        $this->followManager       = $this->container->get('pw_user.follow_manager');
    }

    /**
     * Test that deleting a user also deletes that user's boards, posts, etc.
     */
    public function testDeletingUserCascades()
    {
        /* @var $user \PW\UserBundle\Document\User */
        $user = $this->userManager->getRepository()->findOneByName("User #1");
        $this->userManager->delete($user);

        /* @var $user \PW\UserBundle\Document\User */
        $user = $this->userManager->getRepository()->findOneByName("User #1");
        $this->assertNotNull($user->getDeleted());

        $userFollowers = $this->followManager->getRepository()
            ->findFollowersByUser($user, array('includeDeleted' => true))
            ->getQuery()->execute();

        $this->assertGreaterThan(0, $userFollowers->count());

        foreach ($userFollowers as $follower /* @var $follower \PW\UserBundle\Document\Follow */) {
            $this->assertFalse($follower->getIsActive());
        }

        $userBoards = $this->boardManager->getRepository()
            ->findByUser($user, array('includeDeleted' => true))
            ->getQuery()->execute();

        $this->assertGreaterThan(0, $userBoards->count());

        foreach ($userBoards as $board /* @var $board \PW\BoardBundle\Document\Board */) {
            $this->assertFalse($board->getIsActive());

            $boardFollowers = $this->followManager->getRepository()
                ->findFollowersByBoard($board, array('includeDeleted' => true))
                ->getQuery()->execute();

            foreach ($boardFollowers as $follower /* @var $follower \PW\UserBundle\Document\Follow */) {
                $this->assertFalse($follower->getIsActive());
            }

            $boardPosts = $this->postManager->getRepository()
                ->findByBoard($board, array('includeDeleted' => true))
                ->getQuery()->execute();

            $this->assertGreaterThan(0, $boardPosts->count());

            foreach ($boardPosts as $post /* @var $post \PW\PostBundle\Document\Post */) {
                $this->assertFalse($post->getIsActive());
            }
        }
    }
}
