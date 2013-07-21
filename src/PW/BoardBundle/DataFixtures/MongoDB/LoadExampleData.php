<?php

namespace PW\BoardBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager,
    PW\BoardBundle\Document\Board;

/**
 * LoadExampleData
 */
class LoadExampleData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * getOrder
     *
     * We need basically everything to be loaded first
     *
     * @return int
     */
    public function getOrder()
    {
        return 60;
    }

    /**
     * load some example data
     *
     * @param mixed $manager instance
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 10; $i++) {
            $document = new Board();
            $document->setCreatedBy($this->getReference("User-$i"));
            $cat = $i;
            $document->setCategory($this->getReference("Category-$cat"));
            $document->setIsActive(true);
            $document->setIsPublic($i == 6);
            $document->setIsSystem($i < 3);
            $document->setName('Board ' . $i);
            $document->setTags(
                array(
                    'system',
                    'tag'
                )
            );
            $this->addReference("Board-$i", $document);
            $manager->persist($document);
        }

        $manager->flush();
    }
}
