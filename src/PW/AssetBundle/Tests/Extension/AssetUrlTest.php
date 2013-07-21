<?php

namespace PW\AssetBundle\Tests;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\AssetBundle\Extension\AssetUrl,
    PW\AssetBundle\Command\AssetVersionCommand,
    Symfony\Component\Console\Output\NullOutput;

/**
 * AssetUrlTest
 */
class AssetUrlTest extends AbstractTest
{

    /**
     * setUp
     */
    public function setUp()
    {
        $this->filter = new AssetUrl();
    }

    /**
     * testRelativeUrlWithExistingFile
     */
    public function testRelativeUrlWithExistingFile()
    {
        $input = 'somelocalfile.png';
        $input = '/../src/PW/AssetBundle/Tests/assets/500x500.png';
        $output = $this->filter->version($input, 'large');
        $this->assertSame('/../src/PW/AssetBundle/Tests/assets/500x500.l.png', $output);
        
    }

    /**
     * testRelativeUrlWithNonExistentFile
     */
    public function testRelativeUrlWithNonExistentFile()
    {
        $input = 'somelocalfile.png';
        $output = $this->filter->version($input, 'large');
        $this->assertSame('somelocalfile.png', $output);
    }

    /**
     * testFullUrl
     */
    public function testFullUrl()
    {
        $input = 'http://some.domain.com/over/here/somelocalfile.png';
        $output = $this->filter->version($input, 'large');
        $this->assertSame('http://some.domain.com/over/here/somelocalfile.l.png', $output);
    }
}
