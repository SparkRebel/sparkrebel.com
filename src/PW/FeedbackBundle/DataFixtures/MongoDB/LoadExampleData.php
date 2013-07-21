<?php

namespace PW\FeedbackBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager,
    PW\FeedbackBundle\Document\Feedback;

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
            $document = new Feedback();
            $document->setName($i);
            $manager->persist($document);
        }

        $manager->flush();
    }
}
