<?php

namespace PW\FlagBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager,
    PW\FlagBundle\Document\Flag;

/**
 * LoadExampleData 
 */
class LoadExampleData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * getOrder 
     *
     * Everything should exist before you can flag
     *
     * @return int
     */
    public function getOrder()
    {
        return 100;
    }

    /**
     * load some example data
     * 
     * @param mixed $manager instance
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 10; $i++) {
            $document = new Flag();
            $document->setTarget($this->getReference("Post-$i"));
            $document->setTargetUser($this->getReference("User-$i"));
            $document->setReason($i);
            $manager->persist($document);
        }

        $manager->flush();
    }
}
