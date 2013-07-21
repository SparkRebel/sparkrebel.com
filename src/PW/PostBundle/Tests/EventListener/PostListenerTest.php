<?php

namespace PW\PostBundle\Tests\EventListener;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\PostBundle\Document\Post,
    PW\UserBundle\Document\User;

/**
 * PostListenerTest
 */
class PostListenerTest extends AbstractTest
{
    protected $_repository;

    /**
     * fixtures to load before each test
     */
    protected $_fixtures = array(
        'PW\AssetBundle\DataFixtures\MongoDB\LoadExampleData',
        'PW\BoardBundle\DataFixtures\MongoDB\LoadExampleData',
        'PW\CategoryBundle\DataFixtures\MongoDB\LoadExampleData',
        'PW\ItemBundle\DataFixtures\MongoDB\LoadExampleItems',
        'PW\OutfitBundle\DataFixtures\MongoDB\LoadExampleData',
        'PW\UserBundle\DataFixtures\MongoDB\LoadExampleData',
        'PW\PostBundle\DataFixtures\MongoDB\LoadExamplePosts'
    );

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->_repository = $this->_dm->getRepository('PWPostBundle:Post');
        $this->event = $this->container->get('pw.event');
    }

    /**
     * testPostCreateEvent
     */
    public function testPostCreateEvent()
    {
        $userRepo = $this->_dm->getRepository('PWUserBundle:User');
        $user = $userRepo->findOneBySlug('testuser1');

        $p100 = new Post();
        $p100->setCreatedBy($user);
        $p100->setDescription('1.0.0');
        $this->_dm->persist($p100);
        $this->_dm->flush();

        $counts = array(
            'post.create' => 0,
        );
        $events = $this->event->getRequests('publish');
        foreach ($events as $event) {
            if (!isset($counts[$event['event']])) {
                $counts[$event['event']] = 0;
            }
            $counts[$event['event']]++;
        }

        $this->assertSame(1, $counts['post.create'], 'Expected 1 post.create event to be emitted');
        $this->assertEquals(array(
            'postId' => $p100->getId(),
            'userId' => $user->getId()
        ), $events[0]['message']);
    }

    /**
     * testRepostEvent
     */
    public function testRepostEvent()
    {
        $original = $this->_repository->createQueryBuilder()
            ->field('link')->equals("/relative/link/1")
            ->field('original')->exists(false)
            ->getQuery()->execute()
            ->getSingleResult();

        $userRepo = $this->_dm->getRepository('PWUserBundle:User');
        $user = $userRepo->findOneBySlug('testuser1');

        $p110 = new Post();
        $p110->setCreatedBy($user);
        $p110->setDescription('1.1.0');
        $p110->setParent($original);
        $this->_dm->persist($p110);
        $this->_dm->flush();

        $counts = array(
            'post.create' => 0,
            'post.repost' => 0,
        );
        $events = $this->event->getRequests('publish');
        foreach ($events as $event) {
            if (!isset($counts[$event['event']])) {
                $counts[$event['event']] = 0;
            }
            $counts[$event['event']]++;
        }

        $this->assertSame(1, $counts['post.create'], 'Expected 1 post.create event to be emitted');
        $this->assertEquals(array(
            'postId' => $p110->getId(),
            'userId' => $user->getId()
        ), $events[0]['message']);

        $this->assertSame(1, $counts['post.repost'], 'Expected 1 post.repost event to be emitted');
        $this->assertEquals(array(
            'postId' => $p110->getId(),
            'userId' => $user->getId(),
            'parentPostId' => $original->getId(),
            'parentUserId' => $original->getCreatedBy()->getId()
        ), $events[1]['message']);
    }

    /**
     * testAutomaticPostsDontPostToFacebook
     *
     * automatic posts shouldn't be posted to facebook and should not have a postedtofacebook
     * date
     */
    public function testAutomaticPostsDontPostToFacebook()
    {
        $userRepo = $this->_dm->getRepository('PWUserBundle:User');
        $user = $userRepo->findOneBySlug('testuser1');

        $p100 = new Post();
        $p100->setCreatedBy($user);
        $p100->setDescription('1.0.0');
        $this->_dm->persist($p100);
        $this->_dm->flush();

        $this->assertFalse((bool) $p100->getPostOnFacebook(), "Posts should only be posted to facebook if explicitly specified to do so");
        $this->assertNull($p100->getPostedToFacebook(), "This field should only be populated if posted to facebook");
    }
}
