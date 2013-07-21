<?php

namespace PW\ItemBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\Persistence\ObjectManager,
    PW\ItemBundle\Document\Whitelist;

/**
 * LoadExampleWhitelistData
 */
class LoadExampleWhitelistData extends AbstractFixture
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $document = new Whitelist();
        $document->setId('Thompson');
        $document->setType('brand');
        $manager->persist($document);    
        
        $document = new Whitelist();
        $document->setId('Alloy.com');
        $document->setType('merchant');
        $manager->persist($document);

        $document = new Whitelist();
        $document->setId('Levi.com');
        $document->setType('merchant');
        $manager->persist($document);

        $manager->flush();
    }
}
