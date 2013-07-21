<?php

namespace PW\UserBundle\Tests\EventListener\Functional;

use PW\ApplicationBundle\Tests\AbstractTest;

class FollowingTest extends AbstractTest
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
    );

    public function setUp()
    {
        parent::setUp();
        $this->userManager   = $this->container->get('pw_user.user_manager');
        $this->boardManager  = $this->container->get('pw_board.board_manager');
        $this->followManager = $this->container->get('pw_user.follow_manager');
    }

    /**
     * Test that following a user also automatically follows all of that user's boards.
     */
    public function testFollowUserCascades()
    {
        /* @var $user1 \PW\UserBundle\Document\User */
        $user1 = $this->userManager->getRepository()->findOneByName("User #1");

        /* @var $user2 \PW\UserBundle\Document\User */
        $user2 = $this->userManager->getRepository()->findOneByName("User #2");

        // This is a test that cascading works. Cascading is excuted by the EventManager
        $this->container->get('pw.event')->setMode('foreground');

        $follow = $this->followManager->addFollower($user1, $user2);
        $this->followManager->update($follow);

        $this->assertEventTriggered('user.follow', array(
            'followerId' => $user1->getId(),
            'targetId'   => $user2->getId(),
        ));

        $user2Boards = $this->boardManager->getRepository()
            ->findByUser($user2)->getQuery()->execute();

        $this->assertGreaterThan(0, $user2Boards->count());

        foreach ($user2Boards as $board /* @var $board \PW\BoardBundle\Document\Board */) {
            $user1BoardFollow = $this->followManager->isFollowing($user1, $board);
            $this->assertNotEmpty($user1BoardFollow);
        }
    }
}
