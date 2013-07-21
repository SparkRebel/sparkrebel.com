<?php

namespace PW\TagBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager,
    PW\TagBundle\Document\Tag;

/**
 * LoadExampleData 
 */
class LoadExampleData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * getOrder 
     *
     * No Pre-load dependencies
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
            $document = new Tag();
            $document->setId($i);
            $manager->persist($document);
        }

        $manager->flush();
    }
}
