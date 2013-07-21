<?php

namespace PW\UserBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\DependentFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager,
    Doctrine\ODM\MongoDB\DocumentManager,
    PW\UserBundle\Document\Follow;

class TestUserFollows extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @return array
     */
    public function getDependencies()
    {
        return array(
            'PW\UserBundle\DataFixtures\MongoDB\TestUsers',
        );
    }

    /**
     * @param \Doctrine\ODM\MongoDB\DocumentManager $dm
     */
    public function load(ObjectManager $dm)
    {
        // For each User...
        for ($userCount = 1; $userCount <= $GLOBALS['FIXTURE_USERS_TOTAL']; $userCount++) {
            if ($this->hasReference("user-{$userCount}")) {

                // ... follow another User
                $nextUser = $userCount + 1;
                if (!$this->hasReference("user-{$nextUser}")) {
                    $nextUser = $userCount - 1;
                }

                $follow = new Follow();
                $follow->setFollower($this->getReference("user-{$userCount}"));
                $follow->setFollowing($this->getReference("user-{$nextUser}"));
                $dm->persist($follow);

            }
        }

        //
        // Make first and last User friends
        $follow = new Follow();
        $follow->setFollower($this->getReference('user-1'));
        $follow->setFollowing($this->getReference('user-' . $GLOBALS['FIXTURE_USERS_TOTAL']));
        $dm->persist($follow);

        $follow = new Follow();
        $follow->setFollower($this->getReference('user-' . $GLOBALS['FIXTURE_USERS_TOTAL']));
        $follow->setFollowing($this->getReference('user-1'));
        $dm->persist($follow);

        $dm->flush();
    }
}
