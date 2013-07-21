<?php

namespace PW\OutfitBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager,
    PW\OutfitBundle\Document\Outfit;

/**
 * LoadExampleData 
 */
class LoadExampleData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * getOrder 
     *
     * Items and Users must exist first
     *
     * @return int
     */
    public function getOrder()
    {
        return 50;
    }

    /**
     * load some example data
     * 
     * @param mixed $manager instance
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 10; $i++) {
            $document = new Outfit();
            $document->setCreatedBy($this->getReference("User-$i"));
            for ($j = 1; $j <= 10; $j++) {
                if ($i === $j) {
                    continue;
                }
                $document->addContributors($this->getReference("User-$j"));
            }
            $document->setName($i);
            if ($i > 1) {
                $document->setParent($this->getReference("Outfit-" . ($i - 1)));
            }
            $this->addReference("Outfit-$i", $document);
            $manager->persist($document);
        }

        $manager->flush();
    }
}
