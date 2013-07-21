<?php

namespace PW\CategoryBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\Persistence\ObjectManager,
    Doctrine\ODM\MongoDB\DocumentManager,
    PW\CategoryBundle\Document\Category;

class TestCategories extends AbstractFixture
{
    /**
     * @param \Doctrine\ODM\MongoDB\DocumentManager $dm
     */
    public function load(ObjectManager $dm)
    {
        if (!isset($GLOBALS['FIXTURE_CATEGORIES_TOTAL'])) {
            if (isset($GLOBALS['FIXTURE_USERS_TOTAL'])) {
                $GLOBALS['FIXTURE_CATEGORIES_TOTAL'] = $GLOBALS['FIXTURE_USERS_TOTAL'];
            } else {
                $GLOBALS['FIXTURE_CATEGORIES_TOTAL'] = 4;
            }
        }

        // Create X Categories
        for ($categoryCount = 1; $categoryCount <= $GLOBALS['FIXTURE_CATEGORIES_TOTAL']; $categoryCount++) {
            $category = new Category();
            $category->setType('user');
            $category->setName("Test Category #{$categoryCount}");

            $dm->persist($category);
            $this->addReference("category-{$categoryCount}", $category);
        }

        $dm->flush();
    }
}
