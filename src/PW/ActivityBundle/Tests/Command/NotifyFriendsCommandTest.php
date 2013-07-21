<?php

namespace PW\ActivityBundle\Tests\Command;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\AssetBundle\Command\AssetVersionCommand,
    Symfony\Component\Console\Output\NullOutput;

/**
 * NotifyFriendsCommandTest
 */
class NotifyFriendsCommandTest extends AbstractTest
{
    protected $_fixtures = array(
        'PW\CategoryBundle\DataFixtures\MongoDB\LoadExampleData',
        'PW\UserBundle\DataFixtures\MongoDB\TestFriends',
        'PW\ActivityBundle\DataFixtures\MongoDB\TestFriendsActivity'
    );

    /**
     * testBoardFollow
     *
     * Wendy follows Billy's board
     * Wendy has one friend - Joe
     * Joe should get a notification that Wendy follows Billy's board.
     */
    public function testBoardFollow()
    {
        $qb = $this->_dm->createQueryBuilder('PWBoardBundle:Board');
        $board = $qb
            ->field('name')->equals('Billy\'s Board')
            ->getQuery()->execute()->getSingleResult();

        $qb = $this->_dm->createQueryBuilder('PWUserBundle:Follow');
        $follow = $qb
            ->field('target')->references($board)
            ->getQuery()->execute()->getSingleResult();

        $qb = $this->_dm->createQueryBuilder('PWActivityBundle:Activity');
        $activity = $qb
            ->field('target')->references($follow)
            ->field('type')->equals('board.follow')
            ->getQuery()->execute()->getSingleResult();

        $return = $this->runCommand('notify:friends', array(
            'id' => $activity->getId()
        ));

        $this->assertContains('board.follow notification created for 1 friends', $return);

        $qb = $this->_dm->createQueryBuilder('PWActivityBundle:Notification');
        $notification = $qb
            ->field('target')->references($follow)
            ->field('category')->equals('friend')
            ->getQuery()->execute()->getSingleResult();

        $this->assertSame($follow->getId(), $notification->getTarget()->getId());
        $this->assertTrue($notification->getIsNew());
        $this->assertSame('Wendy Friendly', $notification->getCreatedBy()->getName());
        $this->assertSame('Joe Schmo', $notification->getUser()->getName());
        $this->assertSame('board.follow', $notification->getType());
        $this->assertSame('friend', $notification->getCategory());
        $this->assertSame('Wendy Friendly is now following Billy\'s Board about 1 second ago', $notification->getText());
    }

    /**
     * testPostCreate
     *
     * Wendy is friends with Joe
     * Joe should get a notificatoin that Wendy has created a new post
     */
    public function testPostCreate()
    {
        $qb = $this->_dm->createQueryBuilder('PWPostBundle:Post');
        $post = $qb
            ->field('description')->equals('Wendy\'s first post')
            ->getQuery()->execute()->getSingleResult();

        $qb = $this->_dm->createQueryBuilder('PWActivityBundle:Activity');
        $activity = $qb
            ->field('target')->references($post)
            ->field('type')->equals('post.create')
            ->getQuery()->execute()->getSingleResult();

        $return = $this->runCommand('notify:friends', array(
            'id' => $activity->getId()
        ));

        $this->assertContains('post.create notification created for 1 friends', $return);

        $qb = $this->_dm->createQueryBuilder('PWActivityBundle:Notification');
        $notification = $qb
            ->field('target')->references($post)
            ->field('category')->equals('friend')
            ->getQuery()->execute()->getSingleResult();

        $this->assertSame($post->getId(), $notification->getTarget()->getId());
        $this->assertTrue($notification->getIsNew());
        $this->assertSame('Wendy Friendly', $notification->getCreatedBy()->getName());
        $this->assertSame('Joe Schmo', $notification->getUser()->getName());
        $this->assertSame('post.create', $notification->getType());
        $this->assertSame('friend', $notification->getCategory());
        $this->assertSame('Wendy Friendly sparked this spark to Wendy\'s Board about 1 second ago', $notification->getText());
    }

    /**
     * testUserFollow
     *
     * Wendy follows Jane and Joe
     * Joe is a friend
     * Joe should get a notification that Wendy follows Jane
     */
    public function testUserFollow()
    {
        $qb = $this->_dm->createQueryBuilder('PWUserBundle:User');
        $user = $qb
            ->field('username')->equals('jane')
            ->getQuery()->execute()->getSingleResult();

        $qb = $this->_dm->createQueryBuilder('PWUserBundle:Follow');
        $follow = $qb
            ->field('target')->references($user)
            ->field('target.$ref')->equals("users")
            ->getQuery()->execute()->getSingleResult();

        $qb = $this->_dm->createQueryBuilder('PWActivityBundle:Activity');
        $activity = $qb
            ->field('target')->references($follow)
            ->field('type')->equals('user.follow')
            ->getQuery()->execute()->getSingleResult();

        $return = $this->runCommand('notify:friends', array(
            'id' => $activity->getId()
        ));

        $this->assertContains('user.follow notification created for 1 friends', $return);

        $qb = $this->_dm->createQueryBuilder('PWActivityBundle:Notification');
        $notification = $qb
            ->field('target')->references($follow)
            ->field('category')->equals('friend')
            ->getQuery()->execute()->getSingleResult();

        $this->assertSame($follow->getId(), $notification->getTarget()->getId());
        $this->assertTrue($notification->getIsNew());
        $this->assertSame('Joe Schmo', $notification->getUser()->getName());
        $this->assertSame('user.follow', $notification->getType());
        $this->assertSame('friend', $notification->getCategory());
        $this->assertSame('Wendy Friendly is now following Jane Plain about 1 second ago', $notification->getText());
    }

    /**
     * testUserFollow
     *
     * Wendy follows Jane and Joe
     * Joe is a friend
     * Wendy should get a READ notification when Joe followed her back
     */
    public function testFriendUserFollow()
    {
        $qb = $this->_dm->createQueryBuilder('PWUserBundle:User');
        $user = $qb
            ->field('username')->equals('joe')
            ->getQuery()->execute()->getSingleResult();

        $wendy = $qb
            ->field('username')->equals('wendy')
            ->getQuery()->execute()->getSingleResult();

        $qb = $this->_dm->createQueryBuilder('PWUserBundle:Follow');
        $follow = $qb
            ->field('target')->references($wendy)
            ->field('follower')->references($user)
            ->getQuery()->execute()->getSingleResult();

        $qb = $this->_dm->createQueryBuilder('PWActivityBundle:Activity');
        $activity = $qb
            ->field('target')->references($follow)
            ->field('type')->equals('user.follow')
            ->getQuery()->execute()->getSingleResult();

        $return = $this->runCommand('notify:friends', array(
            'id' => $activity->getId()
        ));

        $this->assertContains('user.follow notification created for 1 friends', $return);

        $qb = $this->_dm->createQueryBuilder('PWActivityBundle:Notification');
        $notification = $qb
            ->field('target')->references($follow)
            ->field('category')->equals('friend')
            ->getQuery()->execute()->getSingleResult();

        $this->assertSame($follow->getId(), $notification->getTarget()->getId());
        $this->assertFalse($notification->getIsNew());
        $this->assertSame('Wendy Friendly', $notification->getUser()->getName());
        $this->assertSame('user.follow', $notification->getType());
        $this->assertSame('friend', $notification->getCategory());
        $this->assertSame('Joe Schmo is now following Wendy Friendly about 1 second ago', $notification->getText());
    }
}
