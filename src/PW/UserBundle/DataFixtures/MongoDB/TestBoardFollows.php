<?php

namespace PW\UserBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\DependentFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager,
    Doctrine\ODM\MongoDB\DocumentManager,
    PW\UserBundle\Document\Follow;

class TestBoardFollows extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @return array
     */
    public function getDependencies()
    {
        return array(
            'PW\UserBundle\DataFixtures\MongoDB\TestUsers',
            'PW\BoardBundle\DataFixtures\MongoDB\TestBoards',
        );
    }

    /**
     * @param \Doctrine\ODM\MongoDB\DocumentManager $dm
     */
    public function load(ObjectManager $dm)
    {
        // For each User...
        for ($userCount = $GLOBALS['FIXTURE_USERS_TOTAL']; $userCount >= 1; $userCount--) {
            if ($this->hasReference("user-{$userCount}")) {

                // ... follow another User's first Board
                $nextUser = $userCount - 1;
                if (!$this->hasReference("user-{$nextUser}-board-1")) {
                    $nextUser = $userCount + 1;
                }

                $follow = new Follow();
                $follow->setFollower($this->getReference("user-{$userCount}"));
                $follow->setFollowing($this->getReference("user-{$nextUser}-board-1"));
                $dm->persist($follow);

            }
        }

        $dm->flush();
    }
}
