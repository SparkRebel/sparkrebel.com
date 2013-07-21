<?php

namespace PW\BoardBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\DependentFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager,
    Doctrine\ODM\MongoDB\DocumentManager,
    PW\BoardBundle\Document\Board;

class TestBoards extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @return array
     */
    public function getDependencies()
    {
        return array(
            'PW\UserBundle\DataFixtures\MongoDB\TestUsers',
            'PW\CategoryBundle\DataFixtures\MongoDB\TestCategories',
        );
    }

    /**
     * @param \Doctrine\ODM\MongoDB\DocumentManager $dm
     */
    public function load(ObjectManager $dm)
    {
        if (!isset($GLOBALS['FIXTURE_BOARDS_TOTAL'])) {
            $GLOBALS['FIXTURE_BOARDS_TOTAL'] = 2;
        }

        $defaults = array(
            'isActive' => true,
            'isPublic' => true,
            'isSystem' => false,
        );

        // For each User...
        for ($userCount = 1; $userCount <= $GLOBALS['FIXTURE_USERS_TOTAL']; $userCount++) {
            if ($this->hasReference("user-{$userCount}")) {

                // ... create 2 Boards
                for ($boardCount = 1; $boardCount <= $GLOBALS['FIXTURE_BOARDS_TOTAL']; $boardCount++) {
                    $board = new Board();
                    $board->fromArray($defaults);
                    $board->setCategory($this->getReference("category-{$userCount}"));
                    $board->setCreatedBy($this->getReference("user-{$userCount}"));
                    $board->setName("User #{$userCount} - Board #{$boardCount}");

                    $dm->persist($board);
                    $dm->flush($board);
                    $this->addReference("user-{$userCount}-board-{$boardCount}", $board);
                }

            }
        }
    }
}