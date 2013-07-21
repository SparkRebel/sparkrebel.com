<?php

namespace PW\ActivityBundle\Tests\Command;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\AssetBundle\Command\AssetVersionCommand,
    Symfony\Component\Console\Output\NullOutput;

/**
 * ActivityCreateCommandTest
 */
class ActivityCreateCommandTest extends AbstractTest
{
    protected $_fixtures = array(
        'PW\BoardBundle\DataFixtures\MongoDB\TestBoards',
        'PW\CategoryBundle\DataFixtures\MongoDB\TestCategories',
        'PW\PostBundle\DataFixtures\MongoDB\TestPosts',
        'PW\PostBundle\DataFixtures\MongoDB\TestPostActivities',
        'PW\UserBundle\DataFixtures\MongoDB\TestBoardFollows',
        'PW\UserBundle\DataFixtures\MongoDB\TestUsers',
        'PW\UserBundle\DataFixtures\MongoDB\TestUserFollows',
    );

    /**
     * testBoardCreate
     *
     * We don't have a board create activity - but we should
     *
    public function testBoardCreate()
    {
        $qb = $this->_dm->createQueryBuilder('PWBoardBundle:Board');
        $doc = $qb
            ->field('name')->equals('User #1 - Board #1')
            ->getQuery()->execute()->getSingleResult();

        $return = $this->runCommand('activity:create', array(
            'event' => 'board.create',
            'id' => $doc->getId()
        ));

        $this->assertContains('Activity created for board.create', $return);
    }
     */

    /**
     * testBoardFollow
     */
    public function testBoardFollow()
    {
        $qb = $this->_dm->createQueryBuilder('PWBoardBundle:Board');
        $board = $qb
            ->field('name')->equals('User #1 - Board #1')
            ->getQuery()->execute()->getSingleResult();

        $qb = $this->_dm->createQueryBuilder('PWUserBundle:Follow');
        $doc = $qb
            ->field('target')->references($board)
            ->getQuery()->execute()->getSingleResult();

        $return = $this->runCommand('activity:create', array(
            'event' => 'board.follow',
            'id' => $doc->getId()
        ));

        $this->assertContains('Activity created for board.follow', $return);
    }

    /**
     * testCommentCreate
     */
    public function testCommentCreate()
    {
        $qb = $this->_dm->createQueryBuilder('PWPostBundle:PostComment');
        $doc = $qb
            ->field('content')->equals('User #2 - Board #1 - Post #1 - Comment')
            ->getQuery()->execute()->getSingleResult();

        $return = $this->runCommand('activity:create', array(
            'event' => 'comment.create',
            'id' => $doc->getId()
        ));

        $this->assertContains('Activity created for comment.create', $return);
    }

    /**
     * testCommentTag
     */
    public function testCommentTag()
    {
        $qb = $this->_dm->createQueryBuilder('PWUserBundle:User');
        $user = $qb
            ->field('name')->equals('User #1')
            ->getQuery()->execute()->getSingleResult();

        $qb = $this->_dm->createQueryBuilder('PWPostBundle:PostComment');
        $doc = $qb
            ->field('content')->equals('User #2 - Board #1 - Post #1 - Comment')
            ->getQuery()->execute()->getSingleResult();

        $return = $this->runCommand('activity:create', array(
            'event' => 'comment.tag',
            'id' => $doc->getId(),
            'userId' => $user->getId()
        ));

        $this->assertContains('Activity created for comment.tag', $return);
    }

    /**
     * testPostCreate
     */
    public function testPostCreate()
    {
        $qb = $this->_dm->createQueryBuilder('PWPostBundle:Post');
        $doc = $qb
            ->field('description')->equals('User #1 - Board #1 - Post #1')
            ->getQuery()->execute()->getSingleResult();

        $return = $this->runCommand('activity:create', array(
            'event' => 'post.create',
            'id' => $doc->getId()
        ));

        $this->assertContains('Activity created for post.create', $return);
    }

    /**
     * testUserFollow
     */
    public function testUserFollow()
    {
        $qb = $this->_dm->createQueryBuilder('PWUserBundle:User');
        $user = $qb
            ->field('name')->equals('User #1')
            ->getQuery()->execute()->getSingleResult();

        $qb = $this->_dm->createQueryBuilder('PWUserBundle:Follow');
        $doc = $qb
            ->field('target')->references($user)
            ->field('target.$ref')->equals("users")
            ->getQuery()->execute()->getSingleResult();

        $return = $this->runCommand('activity:create', array(
            'event' => 'user.follow',
            'id' => $doc->getId()
        ));

        $this->assertContains('Activity created for user.follow', $return);
    }
}
