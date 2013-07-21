<?php

namespace PW\UserBundle\Tests\EventListener;

use PW\ApplicationBundle\Tests\AbstractTest;

/**
 * UserListenerTest
 */
class UserListenerTest extends AbstractTest
{
    protected $_fixtures = array(
        'LoadExampleData'
    );

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->userManager   = $this->container->get('pw_user.user_manager');
        $this->followManager = $this->container->get('pw_user.follow_manager');
        $this->event         = $this->container->get('pw.event');
    }

    /**
     * testUserFollowEvent
     */
    public function testUserFollowEvent()
    {
        $user = $this->userManager->getRepository()->findOneByName("testuser1");
        $target = $this->userManager->getRepository()->findOneByName("testuser5");

        $follow = $this->followManager->addFollower($user, $target);
        $this->_dm->persist($follow);
        $this->_dm->flush();

        $requests = $this->event->getRequests();

        $event = $requests['publish'][0];
        $this->assertSame('user.follow', $event['event']);
        $expected = array(
            'followId' => $follow->getId(),
            'followerId' => $user->getId(),
            'targetId' => $target->getId(),
            'type' => 'user'
        );
        $this->assertEquals($expected, $event['message']);
    }
}
