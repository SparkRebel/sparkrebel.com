<?php

namespace PW\ItemBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager,
    PW\ItemBundle\Document\FeedItem,
    PW\CategoryBundle\Document\Category;

/**
 * LoadExampleFeedItemData
 */
class FeedItemReducedPrice extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Needs Feed Images and assets
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
        $document = $manager->getRepository('PWItemBundle:FeedItem')
            ->findOneBy(array('fid' => 'fixionalprovider-levi-501'));

        if (!$document) {
            $document->setBrand('Levi');
            $document->setCategories(array('Trousers', 'Bottoms'));
            $document->setDescription('The definitive shrink to fit jeans');
            $document->setFid('fixionalprovider-levi-501');
            $document->setImagePrimary($this->getReference('Asset-1'));
            $document->setName('501');
            $document->setMainImage('http://eu.levi.com/medias/sys_master/product/8834583232542/ss12-redtab-male-14501-0030-f-medium.jpg');
            $document->setImages(
                array(
                    'http://eu.levi.com/medias/sys_master/product/8834583232542/ss12-redtab-male-14501-0030-f-medium.jpg',
                    'http://eu.levi.com/medias/sys_master/product/8834583232542/ss12-redtab-male-14501-0031-f-medium.jpg',
                    'http://eu.levi.com/medias/sys_master/product/8834583232542/ss12-redtab-male-14501-0032-f-medium.jpg',
                    'http://eu.levi.com/medias/sys_master/product/8834583232542/ss12-redtab-male-14501-0033-f-medium.jpg',
                    'http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg'
                )
            );
            $document->setImagesRef(array(
                'http://eu.levi.com/medias/sys_master/product/8834583232542/ss12-redtab-male-14501-0030-f-medium.jpg' => 'hash',
                'http://eu.levi.com/medias/sys_master/product/8834583232542/ss12-redtab-male-14501-0031-f-medium.jpg' => 'hash',
                'http://eu.levi.com/medias/sys_master/product/8834583232542/ss12-redtab-male-14501-0032-f-medium.jpg' => 'hash',
                'http://eu.levi.com/medias/sys_master/product/8834583232542/ss12-redtab-male-14501-0033-f-medium.jpg' => 'hash',
                'http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg' => 'hash'
            ));
            $document->setLink('http://eu.levi.com/es_ES/shop/products/male-Partes-de-abajo-Vaqueros-y-pantalones/501-Jeans-14501-0030.html');
            $document->setMerchant('Levi.com');
            $document->setModified(time());
        }
        $document->setPrice('49');
        $document->setPriceHistory(
            array(
                (time() - 60 * 60 * 24 * 60) => 90,
                (time() - 60 * 60 * 24 * 30) => 95,
                (time() - 60 * 60 * 24 * 10) => 99,
                time() => 49
            )
        );
        $document->setOnSale(true);
        $manager->persist($document);

        $manager->flush();
    }
}
