<?php

namespace PW\AssetBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager,
    PW\AssetBundle\Document\Asset;

/**
 * LoadExampleData
 */
class LoadExampleData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * getOrder
     *
     * Users should exist first, but can be loaded early
     *
     * @return int
     */
    public function getOrder()
    {
        return 30;
    }

    /**
     * load some example data
     *
     * @param mixed $manager instance
     */
    public function load(ObjectManager $manager)
    {
        $document = new Asset();
        $document->setIsActive(true);
        $document->setUrl("/images/items/blank.png");
        $this->addReference("Asset-Blank", $document);
        $manager->persist($document);

        $sources = array(
            'upload',
            'bookmarklet',
            'mobile'
        );
        $j = 0;
        for ($i = 1; $i <= 10; $i++) {
            if ($i > 3) {
                if ($i < 7) {
                    $j = 1;
                } else {
                    $j = 2;
                }
            }
            $document = new Asset();
            $document->setIsActive(true);
            $document->setCreatedBy($this->getReference("User-$i"));
            $document->setMeta(array('price' => $i));
            $document->setSource($sources[$j]);
            $document->setTags(
                array(
                    'system',
                    'tag'
                )
            );
            $o = '';
            if ($i < 10) {
                $o = '0';
            }
            $document->setUrl("/images/items/blank.png");
            $this->addReference("Asset-$i", $document);
            $manager->persist($document);
        }

        $manager->flush();
    }
}
