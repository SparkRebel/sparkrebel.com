<?php

namespace PW\ItemBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager,
    PW\ItemBundle\Document\Item;

class LoadExampleItems extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Needs brands, merchants, and categories
     *
     * @return int
     */
    public function getOrder()
    {
        return 40;
    }

    /**
     * @param mixed $manager instance
     */
    public function load(ObjectManager $manager)
    {
        // Used for References
        $i = 0;

        $i++;
        $document = new Item();
        //$document->setMerchant($this->getReference("Merchant-{$i}"));
        $document->addCategories($this->getReference("Category-{$i}-{$i}"));
        $document->setName('Hayley Jeans');
        $document->setPrice(75.00);
        $document->setPricePrevious(115.00);
        $document->setLink('javascript:alert("go to merchant site");');
        $this->addReference("Item-$i", $document);
        $manager->persist($document);

        $i++;
        $document = new Item();
        //$document->setMerchant($this->getReference("Merchant-{$i}"));
        $document->addCategories($this->getReference("Category-{$i}-{$i}"));
        $document->setName('Serene Beaded Dress');
        $document->setPrice(223.50);
        $document->setLink('javascript:alert("go to merchant site");');
        $this->addReference("Item-$i", $document);
        $manager->persist($document);

        $i++;
        $document = new Item();
        //$document->setMerchant($this->getReference("Merchant-{$i}"));
        $document->addCategories($this->getReference("Category-{$i}-{$i}"));
        $document->setName('Stripe It Lucky Top');
        $document->setPrice(58.00);
        $document->setPricePrevious(62.00);
        $document->setLink('javascript:alert("go to merchant site");');
        $this->addReference("Item-$i", $document);
        $manager->persist($document);

        $i++;
        $document = new Item();
        //$document->setMerchant($this->getReference("Merchant-{$i}"));
        $document->addCategories($this->getReference("Category-{$i}-{$i}"));
        $document->setName('Lace Fedora');
        $document->setPrice(3.00);
        $document->setLink('javascript:alert("go to merchant site");');
        $this->addReference("Item-$i", $document);
        $manager->persist($document);

        $i++;
        $document = new Item();
        //$document->setMerchant($this->getReference("Merchant-{$i}"));
        $document->addCategories($this->getReference("Category-{$i}-{$i}"));
        $document->setName('Poncho Dolman Sleeve Top');
        $document->setPrice(9.25);
        $document->setPricePrevious(10.00);
        $document->setLink('javascript:alert("go to merchant site");');
        $this->addReference("Item-$i", $document);
        $manager->persist($document);

        $i++;
        $document = new Item();
        //$document->setMerchant($this->getReference("Merchant-{$i}"));
        $document->addCategories($this->getReference("Category-{$i}-{$i}"));
        $document->setName('Solid Body Con Dress');
        $document->setPrice(32.50);
        $document->setLink('javascript:alert("go to merchant site");');
        $this->addReference("Item-$i", $document);
        $manager->persist($document);

        $i++;
        $document = new Item();
        //$document->setMerchant($this->getReference("Merchant-{$i}"));
        $document->addCategories($this->getReference("Category-{$i}-{$i}"));
        $document->setName('Colorblock Bandage Dress');
        $document->setPrice(34.50);
        $document->setPricePrevious(42.75);
        $document->setLink('javascript:alert("go to merchant site");');
        $this->addReference("Item-$i", $document);
        $manager->persist($document);

        $i++;
        $document = new Item();
        //$document->setMerchant($this->getReference("Merchant-{$i}"));
        $document->addCategories($this->getReference("Category-{$i}-{$i}"));
        $document->setName('Glitter Jean Belt');
        $document->setPrice(7.00);
        $document->setLink('javascript:alert("go to merchant site");');
        $this->addReference("Item-$i", $document);
        $manager->persist($document);

        $i++;
        $document = new Item();
        //$document->setMerchant($this->getReference("Merchant-{$i}"));
        $document->addCategories($this->getReference("Category-{$i}-{$i}"));
        $document->setName('Amber Cami');
        $document->setPrice(6.50);
        $document->setPricePrevious(8.50);
        $document->setLink('javascript:alert("go to merchant site");');
        $this->addReference("Item-$i", $document);
        $manager->persist($document);

        $i++;
        $document = new Item();
        //$document->setMerchant($this->getReference("Merchant-{$i}"));
        $document->addCategories($this->getReference("Category-{$i}-{$i}"));
        $document->setName('Fold Over Yoga Pant');
        $document->setPrice(12.50);
        $document->setLink('javascript:alert("go to merchant site");');
        $this->addReference("Item-$i", $document);
        $manager->persist($document);

        $manager->flush();
    }
}
