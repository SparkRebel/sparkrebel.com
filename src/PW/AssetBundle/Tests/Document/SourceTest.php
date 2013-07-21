<?php

namespace PW\AssetBundle\Tests\Document;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\AssetBundle\Document\Source;

/**
 * SourceTest
 */
class SourceTest extends AbstractTest
{
    /**
     * @var \PW\AssetBundle\Repository\SourceRepository
     */
    protected $_repository;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->_repository = $this->_dm->getRepository('PWAssetBundle:Source');
    }

    /**
     * Test creating a new Source
     */
    public function testDefaults()
    {
        $doc = new Source();

        $this->_dm->persist($doc);
        $this->_dm->flush();

        $this->assertNotEmpty($doc);
        $this->assertNotEmpty($doc->getId());
        $this->assertTrue($doc->getIsActive());
    }
}
