<?php

namespace PW\ItemBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager,
    PW\ItemBundle\Document\Item,
    PW\UserBundle\Document\Merchant;

/**
 * LoadExampleFeedItemData
 */
class PostStep1Data extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Needs Categories and assets
     *
     * @return int
     */
    public function getOrder()
    {
        return 40;
    }

    /**
     * This is the same main fixture from the main feed item fixture file
     * It has a reduced price
     *
     * @param mixed $manager instance
     */
    public function load(ObjectManager $manager)
    {
        $user = new Merchant();
        $user->setUsername('Levi.com');
        $user->setName('Levi.com');

        $item = new Item();
        $item->addCategories($this->getReference('item-cat-Bottoms-Trousers'));
        $item->setCreatedBy($user);
        $item->setDescription("The definitive shrink to fit jeans");
        $item->setFeedId('fixionalprovider-levi-501');
        $item->setImagePrimary($this->getReference('Asset-1'));
        $item->setIsActive(true);
        $item->setIsDiscontinued(false);
        $item->setIsOnSale(false);
        $item->setLink("http://eu.levi.com/es_ES/shop/products/male-Partes-de-abajo-Vaqueros-y-pantalones/501-Jeans-14501-0030.html");
        $item->setMerchantName('Levi.com');
        $item->setMerchantUser($user);
        $item->setName('501');
        $item->setPrice(99);
        $item->setPricePrevious(95);

        $manager->persist($user);
        $manager->persist($item);

        $manager->flush();
    }
}
