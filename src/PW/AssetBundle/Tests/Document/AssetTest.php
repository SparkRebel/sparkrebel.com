<?php

namespace PW\AssetBundle\Tests\Document;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\AssetBundle\Document\Asset;

/**
 * AssetTest
 */
class AssetTest extends AbstractTest
{
    /**
     * @var \PW\AssetBundle\Repository\AssetRepository
     */
    protected $_repository;

    /**
     * fixtures to load before each test
     */
    protected $_fixtures = array(
        'PW\UserBundle\DataFixtures\MongoDB\LoadExampleData',
        'LoadExampleData'
    );

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->_repository = $this->_dm->getRepository('PWAssetBundle:Asset');
    }

    /**
     * Test creating a new Asset
     */
    public function testDefaults()
    {
        $asset = new Asset();
        $asset->setUrl(mt_rand());

        $this->_dm->persist($asset);
        $this->_dm->flush();

        $this->assertNotEmpty($asset);
        $this->assertNotEmpty($asset->getId());
        $this->assertTrue($asset->getIsActive());
    }

    /**
     * Test reading an Asset
     */
    public function testRead()
    {
        $asset = $this->_repository->findOneByUrl('/images/items/01.png');

        $this->assertNotEmpty($asset);
        $this->assertTrue($asset->getIsActive());
        $this->assertSame(array('price' => 1), $asset->getMeta());
        $this->assertSame('upload', $asset->getSource());
        $this->assertSame(array('system' , 'tag'), $asset->getTags());
        $this->assertSame('/images/items/01.png', $asset->getUrl());
    }
}
