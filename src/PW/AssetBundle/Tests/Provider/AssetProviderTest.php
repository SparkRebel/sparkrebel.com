<?php

namespace PW\AssetBundle\Tests\Provider;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\BoardBundle\Document\Board,
    PW\AssetBundle\Document\Asset;

/**
 * AssetProviderTest
 */
class AssetProviderTest extends AbstractTest
{
    /**
     * fixtures to load before each test
     */
    protected $_fixtures = array(
        'PW\UserBundle\DataFixtures\MongoDB\LoadExampleData',
        'LoadExampleData'
    );

    /**
     * testCreateFromLocalFile
     *
     * Minimal test, check it's possible to create an asset passing a file path
     */
    public function testCreateFromLocalFile()
    {
        $file = 'web/images/logo.png';
        $return = $this->container->get('pw.asset')->addImage($file);
        $hash = sha1_file($file);
        $url = $return->getUrl();
        $this->assertSame("/assets/$hash.png", $url);
    }

    /**
     * testCreateFromLocalFileWithWierdName
     */
    public function testCreateFromLocalFileWithWierdName()
    {
        $file = dirname(__DIR__) . '/assets/0431_7014_913_f_-450$';
        $return = $this->container->get('pw.asset')->addImage($file);
        $hash = sha1_file($file);
        $url = $return->getUrl();
        $this->assertSame("/assets/$hash.jpg", $url);
    }

    /**
     * testCreateFromUrl
     */
    public function testCreateFromUrl()
    {
        $file = 'http://www.google.com/images/srpr/logo3w.png';
        $return = $this->container->get('pw.asset')->addImageFromUrl($file);
        $this->assertSame("google.com", $return->getSourceDomain());
        $this->assertSame($file, $return->getSourceUrl());

        $dm = $this->container->get('doctrine_mongodb.odm.document_manager');
        $sourceRepo = $dm->getRepository('PW\AssetBundle\Document\Source');

        $source = $sourceRepo->findOneBy(array('name' => 'google.com'));
        $this->assertNotNull($source);
        $this->assertSame(1, $source->getAssetCount());
    }
}
