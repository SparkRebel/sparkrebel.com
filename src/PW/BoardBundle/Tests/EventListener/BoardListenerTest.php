<?php

namespace PW\BoardBundle\Tests\EventListener;

use PW\ApplicationBundle\Tests\AbstractTest;

/**
 * BoardListenerTest
 *
 * @group board
 * @group frontend
 */
class BoardListenerTest extends AbstractTest
{
    /**
     * @var \PW\UserBundle\Model\UserManager
     */
    protected $userManager;

    /**
     * @var \PW\CategoryBundle\Model\CategoryManager
     */
    protected $categoryManager;

    /**
     * @var \PW\BoardBundle\Model\BoardManager
     */
    protected $boardManager;

    protected $_fixtures = array(
        'PW\CategoryBundle\DataFixtures\MongoDB\LoadExampleData',
        'PW\UserBundle\DataFixtures\MongoDB\LoadExampleData',
        'LoadExampleData',
        'PW\UserBundle\DataFixtures\MongoDB\TestUsers',
        'PW\UserBundle\DataFixtures\MongoDB\TestUserFollows',
        'PW\CategoryBundle\DataFixtures\MongoDB\TestCategories',
    );

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->userManager    = $this->container->get('pw_user.user_manager');
        $this->categoryManger = $this->container->get('pw_category.category_manager');
        $this->boardManager   = $this->container->get('pw_board.board_manager');
        $this->followManager  = $this->container->get('pw_user.follow_manager');
        $this->event = $this->container->get('pw.event');
    }

    /**
     * testBoardFollowEvent
     */
    public function testBoardFollowEvent()
    {
        $user = $this->userManager->getRepository()->findOneByName("testuser1");
        $board = $this->boardManager->getRepository()->findOneByName("Board 5");

        $follow = $this->followManager->addFollower($user, $board);
        $this->_dm->persist($follow);
        $this->_dm->flush();

        $requests = $this->event->getRequests();

        $event = $requests['publish'][0];
        $this->assertSame('board.follow', $event['event']);
        $expected = array(
            'followId' => $follow->getId(),
            'followerId' => $user->getId(),
            'targetId' => $board->getId(),
            'type' => 'user'
        );
        $this->assertEquals($expected, $event['message']);
    }

    /**
     * testUserFollowersFollowNewBoard
     */
    public function testUserFollowersFollowNewBoard()
    {
        /* @var $user \PW\UserBundle\Document\User */
        /* @var $category \PW\CategoryBundle\Document\Category */

        $this->container->get('pw.event')->setMode('foreground');

        $user = $this->userManager->getRepository()->findOneByName("User #1");
        $category = $this->categoryManger->getRepository()->findOneByName('Test Category #1');
        $board = $this->boardManager->create(array(
            'isActive'  => true,
            'isPublic'  => true,
            'isSystem'  => false,
            'category'  => $category,
            'createdBy' => $user,
            'name'      => 'BoardListenerTest #1',
        ));
        $this->boardManager->update($board);

        $followUser = $this->followManager->getRepository()->createQueryBuilder()
            ->field('target')->references($user)
            ->getQuery()->execute()->getSingleResult();
        $follower = $followUser->getFollower();

        $followsBoard = $this->followManager->getRepository()->createQueryBuilder()
            ->count()
            ->field('follower')->references($follower)
            ->field('target')->references($board)
            ->getQuery()->execute();
        $this->assertTrue((bool) $followsBoard, "Follower has not automatically followed the new board");
    }
}
