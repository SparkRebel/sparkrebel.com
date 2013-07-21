<?php

namespace PW\ActivityBundle\Tests\Command;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\AssetBundle\Command\AssetVersionCommand,
    Symfony\Component\Console\Output\NullOutput;

/**
 * NotifyUserCommandTest
 */
class NotifyUserCommandTest extends AbstractTest
{
    protected $_fixtures = array(
        'PW\CategoryBundle\DataFixtures\MongoDB\LoadExampleData',
        'PW\UserBundle\DataFixtures\MongoDB\TestFriends'
    );

    /**
     * testBoardFollow
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

        $return = $this->runCommand('notify:user', array(
            'event' => 'board.follow',
            'id' => $follow->getId()
        ));

        $this->assertContains('Notification created for board.follow', $return);

        $qb = $this->_dm->createQueryBuilder('PWActivityBundle:Notification');
        $notification = $qb
            ->field('target')->references($follow)
            ->field('category')->equals('user')
            ->getQuery()->execute()->getSingleResult();

        $this->assertSame($follow->getId(), $notification->getTarget()->getId());
        $this->assertSame('Billy Nomates', $notification->getUser()->getName());
        $this->assertSame('board.follow', $notification->getType());
        $this->assertSame('user', $notification->getCategory());
        $this->assertSame('Wendy Friendly is now following your Billy\'s Board collection about 1 second ago', $notification->getText());
    }

    /**
     * testCommentCreate
     */
    public function testCommentCreate()
    {
        $qb = $this->_dm->createQueryBuilder('PWPostBundle:PostComment');
        $comment = $qb
            ->field('content')->equals('Check this out @joe')
            ->getQuery()->execute()->getSingleResult();

        $return = $this->runCommand('notify:user', array(
            'event' => 'comment.create',
            'id' => $comment->getId()
        ));

        $this->assertContains('Notification created for comment.create', $return);

        $qb = $this->_dm->createQueryBuilder('PWActivityBundle:Notification');
        $notification = $qb
            ->field('target')->references($comment)
            ->field('category')->equals('user')
            ->getQuery()->execute()->getSingleResult();

        $this->assertSame($comment->getId(), $notification->getTarget()->getId());
        $this->assertSame('Billy Nomates', $notification->getCreatedBy()->getName());
        $this->assertSame('Wendy Friendly', $notification->getUser()->getName());
        $this->assertSame('comment.create', $notification->getType());
        $this->assertSame('user', $notification->getCategory());
        $this->assertSame('Billy Nomates commented on your spark. about 1 second ago', $notification->getText());
    }

    /**
     * testSelfCommentDoesntNotifySelf
     */
    public function testSelfCommentDoesntNotifySelf()
    {
        $qb = $this->_dm->createQueryBuilder('PWPostBundle:PostComment');
        $comment = $qb
            ->field('content')->equals('Normal Comment')
            ->getQuery()->execute()->getSingleResult();

        $return = $this->runCommand('notify:user', array(
            'event' => 'comment.create',
            'id' => $comment->getId()
        ));

        $this->assertContains('Notification *not* created for comment.create', $return);
    }

    /**
     * testCommentReply
     */
    public function testCommentReply()
    {
        $qb = $this->_dm->createQueryBuilder('PWPostBundle:PostComment');
        $comment = $qb
            ->field('content')->equals('Reply')
            ->getQuery()->execute()->getSingleResult();

        $return = $this->runCommand('notify:user', array(
            'event' => 'comment.reply',
            'id' => $comment->getId()
        ));

        $this->assertContains('Notification created for comment.reply', $return);

        $qb = $this->_dm->createQueryBuilder('PWActivityBundle:Notification');
        $notification = $qb
            ->field('target')->references($comment)
            ->field('category')->equals('user')
            ->getQuery()->execute()->getSingleResult();

        $this->assertSame($comment->getId(), $notification->getTarget()->getId());
        $this->assertSame('Joe Schmo', $notification->getCreatedBy()->getName());
        $this->assertSame('Wendy Friendly', $notification->getUser()->getName());
        $this->assertSame('comment.reply', $notification->getType());
        $this->assertSame('user', $notification->getCategory());
        $this->assertSame('Joe Schmo replied to your comment. about 1 second ago', $notification->getText());
    }

    /**
     * testCommentTag
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

        $return = $this->runCommand('notify:user', array(
            'event' => 'comment.tag',
            'id' => $doc->getId(),
            'userId' => $user->getId()
        ));

        $this->assertContains('Notification created for comment.tag', $return);
    }
     */

    /**
     * testPostRepost
     */
    public function testPostRepost()
    {
        $qb = $this->_dm->createQueryBuilder('PWPostBundle:Post');
        $post = $qb
            ->field('description')->equals('Billy\'s repost')
            ->getQuery()->execute()->getSingleResult();

        $return = $this->runCommand('notify:user', array(
            'event' => 'post.repost',
            'id' => $post->getId()
        ));

        $this->assertContains('Notification created for post.repost', $return);

        $qb = $this->_dm->createQueryBuilder('PWActivityBundle:Notification');
        $notification = $qb
            ->field('target')->references($post)
            ->field('category')->equals('user')
            ->getQuery()->execute()->getSingleResult();

        $this->assertSame($post->getId(), $notification->getTarget()->getId());
        $this->assertSame('Billy Nomates', $notification->getCreatedBy()->getName());
        $this->assertSame('Wendy Friendly', $notification->getUser()->getName());
        $this->assertSame('post.repost', $notification->getType());
        $this->assertSame('user', $notification->getCategory());
        $this->assertSame('Billy Nomates resparked your spark to Billy\'s Board about 1 second ago', $notification->getText());

        $qb = $this->_dm->createQueryBuilder('PWPostBundle:Post');
        $doc = $qb
            ->field('description')->equals('User #1 - Board #1 - Post #1')
            ->getQuery()->execute()->getSingleResult();

        $this->assertContains('Notification created for post.repost', $return);
    }

    /**
     * testUserFollow
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
            ->getQuery()->execute()->getSingleResult();

        $return = $this->runCommand('notify:user', array(
            'event' => 'user.follow',
            'id' => $follow->getId()
        ));

        $this->assertContains('Notification created for user.follow', $return);

        $qb = $this->_dm->createQueryBuilder('PWActivityBundle:Notification');
        $notification = $qb
            ->field('target')->references($follow)
            ->field('category')->equals('user')
            ->getQuery()->execute()->getSingleResult();

        $this->assertSame($follow->getId(), $notification->getTarget()->getId());
        $this->assertSame('Jane Plain', $notification->getUser()->getName());
        $this->assertSame('user.follow', $notification->getType());
        $this->assertSame('user', $notification->getCategory());
        $this->assertSame('Wendy Friendly is now following you. about 1 second ago', $notification->getText());
    }
}
