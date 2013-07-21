<?php

namespace PW\AssetBundle\Tests\Command;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\AssetBundle\Command\AssetVersionCommand,
    Symfony\Component\Console\Output\NullOutput;

/**
 * AssetVersionCommandTest
 */
class AssetVersionCommandTest extends AbstractTest
{
    /**
     * testAssets
     *
     * Where are the test assets located
     */
    protected $testAssets;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->testAssets = dirname(__DIR__) . '/assets/';
    }

    /**
     * testCreatesFiles
     *
     * Check the config has 5 items in it - this is just to ensure that the subsequent tests cover
     * the same cases specified in the configuration
     *
     * Run the asset:version command and ensure that files are generated for each version configured.
     */
    public function testCreatesFiles()
    {
        $tmpdir = sys_get_temp_dir() . '/' . preg_replace('@\W+@', '', __METHOD__) . '/';
        if (is_dir($tmpdir)) {
            `rm -rf $tmpdir`;
        }
        mkdir($tmpdir);

        $versions = $this->container->getParameter('imagine.filters');
        $this->assertSame(5, count($versions));

        $this->assertFalse(file_exists($tmpdir . '500x500.t.png'));
        $this->assertFalse(file_exists($tmpdir . '500x500.s.png'));
        $this->assertFalse(file_exists($tmpdir . '500x500.m.png'));
        $this->assertFalse(file_exists($tmpdir . '500x500.l.png'));
        $this->assertFalse(file_exists($tmpdir . '500x500.f.png'));

        $file =  '500x500.png';
        copy($this->testAssets . $file, $tmpdir . $file);
        $return = $this->runCommand('asset:version', array('assetId' => $tmpdir . $file));

        $this->assertTrue(file_exists($tmpdir . '500x500.t.png'));
        $this->assertTrue(file_exists($tmpdir . '500x500.s.png'));
        $this->assertTrue(file_exists($tmpdir . '500x500.m.png'));
        $this->assertTrue(file_exists($tmpdir . '500x500.l.png'));
        $this->assertTrue(file_exists($tmpdir . '500x500.f.png'));

        `rm -rf $tmpdir`;
    }

    /**
     * testExpectedDimensionsSquare
     */
    public function testExpectedDimensionsSquare()
    {
        $tmpdir = sys_get_temp_dir() . '/' . preg_replace('@\W+@', '', __METHOD__) . '/';
        if (is_dir($tmpdir)) {
            `rm -rf $tmpdir`;
        }
        mkdir($tmpdir);

        $size = '2500x2500';
        $file =  $size . '.png';
        copy($this->testAssets . $file, $tmpdir . $file);
        $return = $this->runCommand('asset:version', array('assetId' => $tmpdir . $file));


        $this->assertTrue(file_exists($tmpdir . "$size.t.png"));
        $dims = getimagesize($tmpdir . "$size.t.png");
        $this->assertSame(array(50, 50), array($dims[0], $dims[1]));

        $this->assertTrue(file_exists($tmpdir . "$size.s.png"));
        $dims = getimagesize($tmpdir . "$size.s.png");
        $this->assertSame(array(80, 80), array($dims[0], $dims[1]));

        $this->assertTrue(file_exists($tmpdir . "$size.m.png"));
        $dims = getimagesize($tmpdir . "$size.m.png");
        $this->assertSame(array(180, 180), array($dims[0], $dims[1]));

        $this->assertTrue(file_exists($tmpdir . "$size.l.png"));
        $dims = getimagesize($tmpdir . "$size.l.png");
        $this->assertSame(array(550, 550), array($dims[0], $dims[1]));

        $this->assertTrue(file_exists($tmpdir . "$size.f.png"));
        $dims = getimagesize($tmpdir . "$size.f.png");
        $this->assertSame(array(2000, 2000), array($dims[0], $dims[1]));

        `rm -rf $tmpdir`;
    }

    /**
     * testExpectedDimensionsSquare
     */
    public function testExpectedDimensionsWide()
    {
        $tmpdir = sys_get_temp_dir() . '/' . preg_replace('@\W+@', '', __METHOD__) . '/';
        if (is_dir($tmpdir)) {
            `rm -rf $tmpdir`;
        }
        mkdir($tmpdir);

        $size = '2500x1500';
        $file =  $size . '.png';
        copy($this->testAssets . $file, $tmpdir . $file);
        $return = $this->runCommand('asset:version', array('assetId' => $tmpdir . $file));

        $this->assertTrue(file_exists($tmpdir . "$size.t.png"));
        $dims = getimagesize($tmpdir . "$size.t.png");
        $this->assertSame(array(50, 50), array($dims[0], $dims[1]));

        $this->assertTrue(file_exists($tmpdir . "$size.s.png"));
        $dims = getimagesize($tmpdir . "$size.s.png");
        $this->assertSame(array(80, 80), array($dims[0], $dims[1]));

        $this->assertTrue(file_exists($tmpdir . "$size.m.png"));
        $dims = getimagesize($tmpdir . "$size.m.png");
        $this->assertSame(array(180, 180), array($dims[0], $dims[1]));

        $this->assertTrue(file_exists($tmpdir . "$size.l.png"));
        $dims = getimagesize($tmpdir . "$size.l.png");
        $this->assertSame(array(550, 330), array($dims[0], $dims[1]));

        $this->assertTrue(file_exists($tmpdir . "$size.f.png"));
        $dims = getimagesize($tmpdir . "$size.f.png");
        $this->assertSame(array(2000, 1200), array($dims[0], $dims[1]));

        `rm -rf $tmpdir`;
    }

    /**
     * testExpectedDimensionsWide
     */
    public function testExpectedDimensionsTall()
    {
        $tmpdir = sys_get_temp_dir() . '/' . preg_replace('@\W+@', '', __METHOD__) . '/';
        if (is_dir($tmpdir)) {
            `rm -rf $tmpdir`;
        }
        mkdir($tmpdir);

        $size = '1500x2500';
        $file =  $size . '.png';
        copy($this->testAssets . $file, $tmpdir . $file);
        $return = $this->runCommand('asset:version', array('assetId' => $tmpdir . $file));

        $dims = getimagesize($tmpdir . "$size.t.png");
        $this->assertSame(array(50, 50), array($dims[0], $dims[1]));

        $dims = getimagesize($tmpdir . "$size.s.png");
        $this->assertSame(array(80, 80), array($dims[0], $dims[1]));

        $dims = getimagesize($tmpdir . "$size.m.png");
        $this->assertSame(array(180, 180), array($dims[0], $dims[1]));

        $dims = getimagesize($tmpdir . "$size.l.png");
        $this->assertSame(array(550, 917), array($dims[0], $dims[1]));

        $dims = getimagesize($tmpdir . "$size.f.png");
        $this->assertSame(array(1200, 2000), array($dims[0], $dims[1]));

        `rm -rf $tmpdir`;
    }

    /**
     * testExpectedDimensionsSmall
     */
    public function testExpectedDimensionsSmall()
    {
        $tmpdir = sys_get_temp_dir() . '/' . preg_replace('@\W+@', '', __METHOD__) . '/';
        if (is_dir($tmpdir)) {
            `rm -rf $tmpdir`;
        }
        mkdir($tmpdir);

        $size = '500x500';
        $file =  $size . '.png';
        copy($this->testAssets . $file, $tmpdir . $file);
        $return = $this->runCommand('asset:version', array('assetId' => $tmpdir . $file));

        $this->assertTrue(file_exists($tmpdir . "$size.t.png"));
        $dims = getimagesize($tmpdir . "$size.t.png");
        $this->assertSame(array(50, 50), array($dims[0], $dims[1]));

        $this->assertTrue(file_exists($tmpdir . "$size.s.png"));
        $dims = getimagesize($tmpdir . "$size.s.png");
        $this->assertSame(array(80, 80), array($dims[0], $dims[1]));

        $this->assertTrue(file_exists($tmpdir . "$size.m.png"));
        $dims = getimagesize($tmpdir . "$size.m.png");
        $this->assertSame(array(180, 180), array($dims[0], $dims[1]));

        $this->assertTrue(file_exists($tmpdir . "$size.l.png"));
        $dims = getimagesize($tmpdir . "$size.l.png");
        $this->assertSame(array(500, 500), array($dims[0], $dims[1]));

        $this->assertTrue(file_exists($tmpdir . "$size.f.png"));
        $dims = getimagesize($tmpdir . "$size.f.png");
        $this->assertSame(array(500, 500), array($dims[0], $dims[1]));

        `rm -rf $tmpdir`;
    }

}
