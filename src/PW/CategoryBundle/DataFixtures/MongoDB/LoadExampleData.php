<?php

namespace PW\CategoryBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager,
    PW\CategoryBundle\Document\Category;

/**
 * LoadExampleData
 */
class LoadExampleData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * getOrder
     *
     * Has no pre-load dependencies
     *
     * @return int
     */
    public function getOrder()
    {
        return 10;
    }

    /**
     * load some example data
     *
     * @param mixed $manager instance
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 10; $i++) {
            $document = new Category();
            $document->setName("channel $i");
            $document->setType('user');
            $this->addReference("Category-$i", $document);
            $manager->persist($document);

            for ($j = 1; $j <= 10; $j++) {
                $child = new Category();
                $child->setName("channel $i.$j");
                $child->setType('user');
                $child->setParent($document);
                $this->addReference("Category-$i-$j", $child);
                $manager->persist($child);
            }
        }

        for ($i = 1; $i <= 10; $i++) {
            $document = new Category();
            $document->setName("item channel $i");
            $document->setType('item');
            $this->addReference("ItemCategory-$i", $document);
            $manager->persist($document);
        }

        $itemCats = array(
            'tops' => array(
                'hoodies & sweatshirts',
                'shirts & blouses',
                'sweaters',
                'tanks & camis',
                't-shirts & polos',
            ),
            'Bottoms' => array(
                'jeans',
                'Trousers'
            ),
            'dresses' => array(
            ),
            'outerwear' => array(
            ),
            'swimwear' => array(
            ),
            'shoes' => array(
            ),
            'accessories' => array(
                'belts'
            ),
            'jewelry' => array(
            ),
            'makeup' => array(
            ),
        );

        foreach ($itemCats as $name => $subCats) {
            $document = new Category();
            $document->setName($name);
            $document->setType('item');
            $this->addReference("item-cat-$name", $document);
            $manager->persist($document);

            if (count($subCats) > 0) {
                foreach ($subCats as $subName) {
                    $subDoc = new Category();
                    $subDoc->setName($subName);
                    $subDoc->setType('item');
                    $subDoc->setParent($document);
                    $this->addReference("item-cat-$name-$subName", $subDoc);
                    $manager->persist($subDoc);
                }
            }
        }

        $manager->flush();
    }
}
