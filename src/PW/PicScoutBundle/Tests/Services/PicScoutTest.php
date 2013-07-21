<?php

namespace PW\PicScoutBundle\Tests\Services;

use PW\ApplicationBundle\Tests\AbstractTest;
use PW\AssetBundle\Document\Asset;
use PW\PicScoutBundle\Services\PicScout;

class PicScoutTest extends AbstractTest
{
    
    protected $postManager;
     
    protected $asset;

    protected $picScout;

    public function setUp()
    {
        parent::setUp();
        $this->postManager   = $this->container->get('pw_post.post_manager');
        
        $this->asset = new Asset();                
        $buzz_mock = $this->getMock('Buzz\Browser');
        $buzz_client = $this->getMock('Buzz\Client', array('setTimeout'));
        $buzz_mock->expects($this->once())
            ->method('getClient')
            ->will($this->returnValue($buzz_client));
        $this->picScout = new PicScout(
            'api.picscout.com',
            '1234567890',
            $buzz_mock,
            $this->postManager
        );

        $this->picScout->au = new \PW\AssetBundle\Extension\AssetUrl;

    }

    
    public function testEnsureAbsoluteUri()
    {
        $this->asset->setUrl('/assets/1123asd.jpg');                        
        $this->assertEquals($this->picScout->getPicscoutUrl($this->asset), 'https://api.picscout.com/v1/search?key=1234567890&url=http://sparkrebel.com/assets/1123asd.f.png');
    }

}
