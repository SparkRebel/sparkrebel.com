<?php

namespace PW\UserBundle\Tests\Model;

use PW\ApplicationBundle\Tests\AbstractTest;

/**
 * UserManagerTest
 */
class UserManagerTest extends AbstractTest
{
    protected $_fixtures = array(
        'PW\CategoryBundle\DataFixtures\MongoDB\LoadExampleData',
        'PW\UserBundle\DataFixtures\MongoDB\TestFriends',
        'PW\ActivityBundle\DataFixtures\MongoDB\TestFriendsActivity'
    );

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->userManager = $this->container->get('pw_user.user_manager');
    }

    /**
     * testMakeFriends
     *
     * After calling make friends - both users should be following each other
     */
    public function testMakeFriends()
    {
        $qb = $this->_dm->createQueryBuilder('PWUserBundle:User');
        $billy = $qb
            ->field('username')->equals('billy')
            ->getQuery()->execute()->getSingleResult();

        $qb = $this->_dm->createQueryBuilder('PWUserBundle:User');
        $britney = $qb
            ->field('username')->equals('britney')
            ->getQuery()->execute()->getSingleResult();

        $this->userManager->makeFriends($billy, $britney);

        $qb = $this->_dm->createQueryBuilder('PWUserBundle:Follow');
        $follow = $qb
            ->field('follower')->references($billy)
            ->field('target')->references($britney)
            ->field('isFriend')->equals(true)
            ->getQuery()->execute()->getSingleResult();
        $this->assertTrue((bool) $follow, "No Friend-Follow for Billy following Britney");

        $qb = $this->_dm->createQueryBuilder('PWUserBundle:Follow');
        $follow = $qb
            ->field('follower')->references($britney)
            ->field('target')->references($billy)
            ->field('isFriend')->equals(true)
            ->getQuery()->execute()->getSingleResult();
        $this->assertTrue((bool) $follow, "No Friend-Follow for Britney following Billy");
    }
}
