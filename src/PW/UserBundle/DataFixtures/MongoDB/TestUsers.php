<?php

namespace PW\UserBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\Persistence\ObjectManager,
    PW\UserBundle\Document\User;

class TestUsers extends AbstractFixture
{
    /**
     * @param ObjectManager $dm instance
     */
    public function load(ObjectManager $dm)
    {
        if (!isset($GLOBALS['FIXTURE_USERS_TOTAL'])) {
            $GLOBALS['FIXTURE_USERS_TOTAL'] = 4;
        }

        $defaults = array(
            'plainPassword' => 'test',
            'enabled' => true,
            'created' => new \DateTime(),
        );

        // Create X Users
        for ($userCount = 1; $userCount <= $GLOBALS['FIXTURE_USERS_TOTAL']; $userCount++) {
            $user = new User();
            $user->fromArray($defaults);
            $user->setEmail("testuser{$userCount}@example.com");
            $user->setName("User #{$userCount}");

            $dm->persist($user);
            $this->addReference("user-{$userCount}", $user);
        }

        $dm->flush();
    }
}
