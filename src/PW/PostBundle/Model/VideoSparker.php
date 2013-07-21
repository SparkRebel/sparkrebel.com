<?php

namespace PW\PostBundle\Model;

class VideoSparker
{
    protected $supported_sites = array(
        'youtube.com',
        'vimeo.com'
    );

    protected $host;
    protected $url;
    protected $video_code = null;
    protected $original_host = null;

    public function __construct($url)
    {
        $this->url = $url;
        $this->parseVideoParameters();
    }

    public function getVideoCode()
    {
        return $this->video_code;
    }

    /**
     * Checks if vide code is correctly set
     *
     * @return boolean
     */
    public function isVideoAvilableToFetch()
    {
        return $this->video_code !== null;
    }

    /**
     * checks if given url is youtube/vimeo ets
     *
     * @param string $url
     * @return boolean
     */
    public function isValidVideoUrl()
    {
       if(in_array($this->host, $this->supported_sites) && $this->isVideoAvilableToFetch()) {
           return true;
       }
       return false;
    }

    /**
     * gets video code for url that is being processed
     *
     * @return string $code
     */
    public function getVideoEmbededCode()
    {
        $code = '';
        switch ($this->host) {
            case 'youtube.com':
                $code = self::getYoutubeCode($this->video_code);
                break;
            case 'vimeo.com':
                $code = self::getVimeoCode($this->video_code);
                break;

            default:
                throw new Exception("Video code not avilable, because given url: {$this->url} is not valid video url.", 1);
                break;
        }
        return $code;
    }

    /**
     * returns url of the big thumnail we need to process by ourselves
     *
     * @return string
     */
    public function getBigThumbnailUrl()
    {
        switch ($this->host) {
           case 'youtube.com':
               return  'http://img.youtube.com/vi/'.$this->video_code.'/0.jpg';
               break;

           case 'vimeo.com':
               return $this->getVimeoThumnailUrl();
               break;
       }

    }

    /**
     * static function used in Asset and Post to avoid creting n objects just to get video code for asser
     *
     * @param Asset $asset
     * @return string
     */
    static public function getCodeForVideoAsset(\PW\AssetBundle\Document\Asset $asset)
    {
        $code = '';
        switch ($asset->getHost()) {
            case 'youtube.com':
                $code = self::getYoutubeCode($asset->getVideoCode());
                break;
            case 'vimeo.com':
                $code = self::getVimeoCode($asset->getVideoCode());
                break;

        }
        return $code;
    }

    public static function getYoutubeCode($code)
    {
        return '<iframe src="http://www.youtube.com/embed/'.$code.'?wmode=transparent&autoplay=1&modestbranding=1&rel=0" frameborder="0" allowfullscreen></iframe>';;
    }


    public static function getVimeoCode($code)
    {
        return '<iframe src="http://player.vimeo.com/video/'.$code.'?wmode=transparent&autoplay=1" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
    }

    protected function parseVideoParameters()
    {
        $parts = parse_url($this->url);
        if(!isset($parts['host']))
            return;

        $this->host = str_replace('www.', '', $parts['host']);

        parse_str( parse_url( $this->url, PHP_URL_QUERY ), $arr);

        switch ($this->host) {
            case 'youtube.com':
                $this->original_host = 'youtube.com';
                if(isset($arr['v']) && strlen($arr['v']) > 0)
                    $this->video_code = $arr['v'];
                break;

            case 'vimeo.com':

                if (0 !== preg_match('/^http:\/\/(www\.)?vimeo\.com\/(clip\:)?(\d+).*$/', $this->url, $match)) {
                    $this->original_host = 'vimeo.com';
                    $this->video_code = $match[3];
                }
                break;

            default:
                return false;
                break;
        }
    }

    /**
     * getOriginal_host
     *
     * @return
     */
    public function getOriginalHost()
    {
        return $this->original_host;
    }

    /**
     * setOriginal_host
     *
     * @param mixed $original_host
     * @return VideoSparker
     */
    public function setOriginalHost($original_host)
    {
        $this->original_host = $original_host;
        return $this;
    }

    protected function getVimeoThumnailUrl()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://vimeo.com/api/v2/video/".$this->video_code.".json");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = json_decode(curl_exec($ch));
        $output = $output[0]->thumbnail_large;
        curl_close($ch);
        return $output;
    }
}
