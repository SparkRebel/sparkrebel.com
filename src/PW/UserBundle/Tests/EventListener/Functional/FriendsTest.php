<?php

namespace PW\UserBundle\Tests\EventListener\Functional;

use PW\ApplicationBundle\Tests\AbstractTest;

class FriendsTest extends AbstractTest
{
    /**
     * @var \PW\UserBundle\Model\UserManager
     */
    protected $userManager;

    /**
     * @var \PW\UserBundle\Model\FollowManager
     */
    protected $followManager;

    protected $_fixtures = array(
        'PW\UserBundle\DataFixtures\MongoDB\TestUsers',
        'PW\UserBundle\DataFixtures\MongoDB\TestUserFollows',
    );

    public function setUp()
    {
        parent::setUp();
        $this->userManager   = $this->container->get('pw_user.user_manager');
        $this->followManager = $this->container->get('pw_user.follow_manager');
    }

    /**
     * Test that the isFriend flag is automagically set.
     */
    public function testIsFriendIsSet()
    {
        /* @var $user1 \PW\UserBundle\Document\User */
        $user1 = $this->userManager->getRepository()->findOneByName("User #1");

        /* @var $user2 \PW\UserBundle\Document\User */
        $user2 = $this->userManager->getRepository()->findOneByName("User #2");

        $follow1 = $this->followManager->addFollower($user1, $user2);
        $this->followManager->update($follow1);

        $user1Follow = $this->followManager->isFollowing($user1, $user2);
        $this->assertFalse($user1Follow->getIsFriend());

        $user2Follow = $this->followManager->isFollowing($user2, $user1);
        $this->assertEmpty($user2Follow);

        $follow2 = $this->followManager->addFollower($user2, $user1);
        $this->followManager->update($follow2);

        $user1Follow = $this->followManager->isFollowing($user1, $user2);
        $this->assertTrue($user1Follow->getIsFriend());

        $user2Follow = $this->followManager->isFollowing($user2, $user1);
        $this->assertTrue($user2Follow->getIsFriend());
    }

    /**
     * Test that the isFriend flag is automagically unset.
     */
    public function testIsFriendIsUnset()
    {
        /* @var $user1 \PW\UserBundle\Document\User */
        $user1 = $this->userManager->getRepository()->findOneByName("User #1");

        /* @var $user2 \PW\UserBundle\Document\User */
        $user2 = $this->userManager->getRepository()->findOneByName("User #" . $GLOBALS['FIXTURE_USERS_TOTAL']);

        $this->followManager->removeFollower($user1, $user2);

        $user1Follow = $this->followManager->isFollowing($user1, $user2);
        $this->assertEmpty($user1Follow);

        $user2Follow = $this->followManager->isFollowing($user2, $user1);
        $this->assertFalse($user2Follow->getIsFriend());
    }
}
