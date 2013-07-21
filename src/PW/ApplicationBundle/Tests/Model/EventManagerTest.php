<?php

namespace PW\ApplicationBundle\Tests\Model;

use PW\PostBundle\Document\PostComment;
use PW\ApplicationBundle\Tests\AbstractTest;

/**
 * EventManagerTest
 */
class EventManagerTest extends AbstractTest
{
    /**
     * fixtures to load before each test
     */
    protected $_fixtures = array(
        'PW\CategoryBundle\DataFixtures\MongoDB\LoadExampleData',
        'PW\UserBundle\DataFixtures\MongoDB\TestFriends'
    );

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->event = $this->container->get('pw.event');
    }

    /**
     * testPublish
     */
    public function testPublish()
    {
        $this->event->publish('test', array('ing' => 'one', 'two' => 'one'));

        $requests = $this->event->getRequests();
        $requests = $requests['publish'];

        $this->assertSame(1, count($requests));

        $request = $requests[0];
        $this->assertSame('test', $request['event']);
        $expected = array(
            'ing' => 'one',
            'two' => 'one'
        );
        $this->assertSame($expected, $request['message']);
    }

    /**
     * testCommentCreate
     */
    public function testCommentCreate()
    {
        $qb = $this->_dm->createQueryBuilder('PWUserBundle:User');
        $user = $qb
            ->field('username')->equals('wendy')
            ->getQuery()->execute()->getSingleResult();

        $qb = $this->_dm->createQueryBuilder('PWPostBundle:PostComment');
        $comment = $qb
            ->field('content')->equals('Normal Comment')
            ->getQuery()->execute()->getSingleResult();

        $post = $comment->getPost();

        $this->event->publish('comment.create', array(
            'commentId' => $comment->getId(),
            'userId' => $user->getId(),
            'postId' => $post->getId(),
            'postUserId' => $post->getCreatedBy()->getId(),
        ));

        $requests = $this->event->getRequests();
        $this->assertSame(1, count($requests['publishJob']), 'Expected 1 publishJobs emitted');
        $event = $requests['publishJob'][0];
        $this->assertSame('comment.create', $event['event']);
        $expected = array(
            'commentId' => $comment->getId(),
            'userId' => $user->getId(),
            'postId' => $post->getId(),
            'postUserId' => $post->getCreatedBy()->getId()
        );
        $this->assertEquals($expected, $event['message']);

        $job = $requests['requestJob'][0];
        $expected = "activity:create comment.create " . $comment->getId();
        $this->assertSame($expected, $job['command']);

        $job = $requests['requestJob'][1];
        $expected = "notify:user comment.create " . $comment->getId();
        $this->assertSame($expected, $job['command']);

        $this->assertSame(2, count($requests['requestJob']), 'Expected 2 requestJobs emitted');
    }

    /**
     * testBoardFollow
     */
    public function testBoardFollow()
    {
        $qb = $this->_dm->createQueryBuilder('PWUserBundle:User');
        $user = $qb
            ->field('username')->equals('wendy')
            ->getQuery()->execute()->getSingleResult();

        $qb = $this->_dm->createQueryBuilder('PWBoardBundle:Board');
        $board = $qb
            ->field('name')->equals('Billy\'s Board')
            ->getQuery()->execute()->getSingleResult();

        $qb = $this->_dm->createQueryBuilder('PWUserBundle:Follow');
        $follow = $qb
            ->field('follower')->references($user)
            ->field('target')->references($board)
            ->getQuery()->execute()->getSingleResult();

        $this->event->publish('board.follow', array(
            'followId' => $follow->getId(),
            'followerId' => $user->getId(),
            'targetId' => $board->getId(),
            'type' => 'user'
        ));

        $requests = $this->container->get('pw.event')->getRequests();
        $job = $requests['requestJob'][0];
        $expected = "follow:board " . $user->getId() . ' ' . $board->getId();
        $this->assertSame($expected, $job['command']);

        $job = $requests['requestJob'][1];
        $expected = "activity:create board.follow " . $follow->getId();
        $this->assertSame($expected, $job['command']);

        $job = $requests['requestJob'][2];
        $expected = "notify:user board.follow " . $follow->getId();
        $this->assertSame($expected, $job['command']);

        $this->assertSame(3, count($requests['requestJob']), 'Expected 3 requestJobs emitted');
    }

    /**
     * testPostCreate
     */
    public function testPostCreate()
    {
        $qb = $this->_dm->createQueryBuilder('PWPostBundle:Post');
        $post = $qb
            ->field('description')->equals('Wendy\'s first post')
            ->getQuery()->execute()->getSingleResult();

        $this->event->publish('post.create', array(
            'postId' => $post->getId(),
            'userId' => $post->getCreatedBy()->getId()
        ));

        $requests = $this->container->get('pw.event')->getRequests();
        $job = $requests['requestJob'][0];
        $expected = "follow:post " . $post->getId();
        $this->assertSame($expected, $job['command']);

        $job = $requests['requestJob'][1];
        $expected = "activity:create post.create " . $post->getId();
        $this->assertSame($expected, $job['command']);

        $this->assertSame(2, count($requests['requestJob']), 'Expected 2 requestJobs emitted');
    }

    /**
     * testUserFollow
     */
    public function testUserFollow()
    {
        $qb = $this->_dm->createQueryBuilder('PWUserBundle:User');
        $follower = $qb
            ->field('username')->equals('wendy')
            ->getQuery()->execute()->getSingleResult();

        $qb = $this->_dm->createQueryBuilder('PWUserBundle:User');
        $target = $qb
            ->field('username')->equals('joe')
            ->getQuery()->execute()->getSingleResult();

        $qb = $this->_dm->createQueryBuilder('PWUserBundle:Follow');
        $follow = $qb
            ->field('follower')->references($follower)
            ->field('target')->references($target)
            ->getQuery()->execute()->getSingleResult();

        $this->event->publish('user.follow', array(
            'followId' => $follow->getId(),
            'followerId' => $target->getId(),
            'targetId' => $target->getId(),
            'type' => 'user'
        ));

        $requests = $this->container->get('pw.event')->getRequests();

        $job = $requests['requestJob'][0];
        $expected = "follow:user " . $target->getId() . ' ' . $target->getId();
        $this->assertSame($expected, $job['command']);

        $job = $requests['requestJob'][1];
        $expected = "activity:create user.follow " . $follow->getId();
        $this->assertSame($expected, $job['command']);

        $job = $requests['requestJob'][2];
        $expected = "notify:user user.follow " . $follow->getId();
        $this->assertSame($expected, $job['command']);

        $this->assertSame(3, count($requests['requestJob']), 'Expected 3 requestJobs emitted');
    }
}
