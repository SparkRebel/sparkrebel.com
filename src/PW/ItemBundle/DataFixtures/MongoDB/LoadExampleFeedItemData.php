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
class LoadExampleFeedItemData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Needs Feed Images
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
        $document = new FeedItem();
        $document->setBrand('Levi');
        $document->setCategories(array('Trousers', 'Bottoms'));
        $document->setDescription('The definitive shrink to fit jeans');
        $document->setFid('fixionalprovider-levi-501');
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
        $document->setPrice('99');
        $document->setPriceHistory(
            array(
                (time() - 60 * 60 * 24 * 60) => 90,
                (time() - 60 * 60 * 24 * 30) => 95,
                time() => 99
            )
        );
        $document->setOnSale(false);
        $manager->persist($document);

        //
        $document = new FeedItem();
        $document->setBrand('Thompson');
        $document->setCategories(array('Trousers'));
        $document->setDescription('Wide jeans');
        $document->setFid('fixionalprovider-thompson-wide-jeans');
        $document->setName('Thompson Stretch Wide Leg');
        $document->setMainImage('http://shopimages-pe.alloy.com/images/products/167420_front_cat.jpg');
        $document->setImages(
            array(
                'http://shopimages-pe.alloy.com/images/products/167420_front_cat.jpg'
            )
        );
        $document->setLink('http://click.linksynergy.com/fs-bin/click?id=XA3hZRvo*ks&offerid=172382.51335&type=15&subid=0');
        $document->setMerchant('Alloy.com');
        $document->setModified(1277226584);
        $document->setPrice('32.90');
        $document->setPriceHistory(
            array(
                1277226584 => 32.90,
                1282905486 => 39.00
            )
        );
        $document->setOnSale(false);
        $manager->persist($document);

        //brand == merchant and is merchant
        $document = new FeedItem();
        $document->setBrand('Levi.com');
        $document->setCategories(array('Trousers'));
        $document->setDescription('The definitive shrink to fit jeans');
        $document->setFid('fixionalprovider-levi-502');
        $document->setName('502');
        $document->setMainImage('http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg');
        $document->setImages(
            array(
                'http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg'
            )
        );
        $document->setImagesRef(array(
            'http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg' => 'hash'
        ));
        $document->setLink('http://eu.levi.com/es_ES/shop/products/male-Partes-de-abajo-Vaqueros-y-pantalones/501-Jeans-14501-0030.html');
        $document->setMerchant('Levi.com');
        $document->setModified(time());
        $document->setPrice('32.90');
        $document->setPriceHistory(
            array(
                1277226584 => 32.90,
                1282905486 => 39.00
            )
        );
        $document->setOnSale(false);
        $manager->persist($document);


        //brand == merchant ans id brand
        $document = new FeedItem();
        $document->setBrand('Thompson');
        $document->setCategories(array('Trousers'));
        $document->setDescription('The definitive shrink to fit jeans');
        $document->setFid('fixionalprovider-thompson-503');
        $document->setName('503');
        $document->setMainImage('http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg');
        $document->setImages(
            array(
                'http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg'
            )
        );
        $document->setImagesRef(array(
            'http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg' => 'hash'
        ));
        $document->setLink('http://eu.levi.com/es_ES/shop/products/male-Partes-de-abajo-Vaqueros-y-pantalones/501-Jeans-14501-0030.html');
        $document->setMerchant('Thompson');
        $document->setModified(time());
        $document->setPrice('32.90');
        $document->setPriceHistory(
            array(
                1277226584 => 32.90,
                1282905486 => 34.00
            )
        );
        $document->setOnSale(false);
        $manager->persist($document);



        //merchant != brand, both on whitelist
       $document = new FeedItem();
       $document->setBrand('Thompson');
       $document->setCategories(array('Trousers', 'Bottoms'));
       $document->setDescription('an item whos brand is a brand user and merchant is a merchant user');
       $document->setFid('brand-and-merchant-good');
       $document->setName('100');
       $document->setMainImage('http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg');
       $document->setImages(
           array(         'http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg'
           )
       );
       $document->setImagesRef(array(
'http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg' => 'hash'
       ));
       $document->setLink('http://eu.levi.com/es_ES/shop/products/male-Partes-de-abajo-Vaqueros-y-pantalones/501-Jeans-14501-0030.html');
       $document->setMerchant('Levi.com');
       $document->setModified(time());
       $document->setPrice('1');
       $manager->persist($document);


       //merchant != brand, merchant not on whitelist
      $document = new FeedItem();
      $document->setBrand('Thompson');
      $document->setCategories(array('Trousers'));
      $document->setDescription('an item whos brand is a brand user and merchant is a merchant user');
      $document->setFid('brand-good-merchant-bad');
      $document->setName('101');
      $document->setMainImage('http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg');
      $document->setImages(
          array(         'http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg'
          )
      );
      $document->setImagesRef(array(
'http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg' => 'hash'
      ));
      $document->setLink('http://eu.levi.com/es_ES/shop/products/male-Partes-de-abajo-Vaqueros-y-pantalones/501-Jeans-14501-0030.html');
      $document->setMerchant('notonwhitelist');
      $document->setModified(time());
      $document->setPrice('1');
      $manager->persist($document);


         //merchant != brand, brand not on whitelist
        $document = new FeedItem();
        $document->setBrand('notonwhitelist');
        $document->setCategories(array('Trousers'));
        $document->setDescription('an item whos brand is a brand user and merchant is a merchant user');
        $document->setFid('merchant-good-brand-bad');
        $document->setName('101');
        $document->setMainImage('http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg');
        $document->setImages(
            array(         'http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg'
            )
        );
        $document->setImagesRef(array(
        'http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg' => 'hash'
        ));
        $document->setLink('http://eu.levi.com/es_ES/shop/products/male-Partes-de-abajo-Vaqueros-y-pantalones/501-Jeans-14501-0030.html');
        $document->setMerchant('Levi.com');
        $document->setModified(time());
        $document->setPrice('1');
        $manager->persist($document);


         //no brand set
        $document = new FeedItem();
        $document->setBrand('');
        $document->setCategories(array('Trousers'));
        $document->setDescription('an item whos brand is a brand user and merchant is a merchant user');
        $document->setFid('no-brand-set');
        $document->setName('100');
        $document->setMainImage('http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg');
        $document->setImages(
            array(         'http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg'
            )
        );
        $document->setImagesRef(array(
        'http://eu.levi.com/medias/sys_master/product/8834583429150/ss12-redtab-male-14501-0034-f-medium.jpg' => 'hash'
        ));
        $document->setLink('http://eu.levi.com/es_ES/shop/products/male-Partes-de-abajo-Vaqueros-y-pantalones/501-Jeans-14501-0030.html');
        $document->setMerchant('Thompson');
        $document->setModified(time());
        $document->setPrice('1');
        $manager->persist($document);

       $manager->flush();
    }
}
