<?php 
namespace PW\PostBundle\Tests\Model;

use PW\PostBundle\Model\VideoSparker;


class VideoSparkerTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @dataProvider youtubeProvider
     */
    public function testIsValidYoutubeVideoUrl($url, $expected)
    {
        $s = new VideoSparker($url);
        $this->assertEquals($s->isValidVideoUrl(), $expected);
    }
    
    public function testVideoCode()
    {
         $s = new VideoSparker("http://www.youtube.com/watch?v=Fe2-Tnw-65E&feature=g-vrec");
         $this->assertEquals($s->getVideoCode(), 'Fe2-Tnw-65E');
         
         $s2 = new VideoSparker("http://www.vimeo.com/43043051");
         $this->assertEquals($s2->getVideoCode(), '43043051');
         
    }
    
    /**
     * @dataProvider vimeoProvider
     */
    public function testIsValidVimeoVideoUrl($url, $expected)
    {
        $s = new VideoSparker($url);
        $this->assertEquals($s->isValidVideoUrl(), $expected);
    }
    

    
    public function youtubeProvider()
    {
        return array(
            array('youtube.com', false),
            array('www.youtube.com', false),
            array('http://www.youtube.com/watch?v=Fe2-Tnw-65E&feature=g-vrec', true),
            array('http://youtube.com/watch?v=Fe2-Tnw-65E&feature=g-vrec', true),
            array('youtube.com/watch?v=Fe2-Tnw-65E', false) 
        );
    }
    
    public function vimeoProvider()
    {
        return array(
            array('vimeo.com', false),
            array('www.vimeo.com', false),  
            array('http://vimeo.com/43043051', true),
            array('http://www.vimeo.com/43043051', true)                    
        );
    }
    
}