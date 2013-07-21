<?php

namespace PW\PostdBundle\Tests\EventListener;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\PostBundle\Document\PostActivity,
    PW\UserBundle\Document\User;

/**
 * PostActivityListenerTest
 */
class PostActivityListenerTest extends AbstractTest
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
     * testPostActivityCreateEvent
     */
    public function testPostActivityCreateEvent()
    {
        $post = $this->_repository->createQueryBuilder()
            ->field('link')->equals("/relative/link/1")
            ->field('original')->exists(false)
            ->getQuery()->execute()
            ->getSingleResult();

        $userRepo = $this->_dm->getRepository('PWUserBundle:User');
        $user = $userRepo->findOneBySlug('testuser2');

        $activity = new PostActivity();
        $activity->setCreatedBy($user);
        $activity->setPost($post);
        $activity->setContent('a new activity');

        $this->_dm->persist($activity);
        $this->_dm->flush();

        $requests = $this->event->getRequests();
        $event = $requests['publish'][0];
        $this->assertSame('activity.create', $event['event']);
        $expected = array(
            'activityId' => $activity->getId(),
            'userId' => $user->getId(),
            'postId' => $post->getId(),
            'postUserId' => $post->getCreatedBy()->getId()
        );
        $this->assertEquals($expected, $event['message']);

        $this->assertSame(1, count($requests['publish']), 'Expected 1 events to be emitted');
    }
}
