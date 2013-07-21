<?php

namespace PW\AssetBundle\Provider;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\File\UploadedFile,
    PW\AssetBundle\Document\Asset,
    PW\AssetBundle\Document\Source;

/**
 * AssetProvider
 */
class AssetProvider
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;

    /**
     * @var \PW\ApplicationBundle\Model\EventManager
     */
    protected $event;

    /**
     * @var \PW\AssetBundle\Handler\Common
     */
    protected $handler;
    
    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $path = '/assets/';

    const JPEG_QUALITY = 80; 
    const EXT = '.jpg'; 

    /**
     * Called automatically with variables from parameters.yml. Sets which handler to use and where
     * to store uploaded files (if using the File uploader);
     *
     * @param string $handler S3 or File
     * @param array  $params  passed to the handler contstructor
     */
    public function setHandler($handler, $params = array())
    {
        $class = "\\PW\\AssetBundle\\Handler\\" . ucfirst($handler);
        if (!class_exists($class)) {
            throw new \RuntimeException('$handler is not valid');
        }

        $this->handler = new $class($params);
    }

    /**
     * addFeedImage
     *
     * @param string $img    The url for the original image
     * @param string $url    The original url of the feed item it's for
     * @param string $hash   The hash according to the feedeater for the original image
     * @param array  $params Additional params to pass to addImage
     *
     * @return Asset document
     */
    public function addFeedImage($img, $url, $hash, $params = array())
    {
        $assetsDir = '/var/feeds/assets'; //$this->getContainer()->getParameterBag('pw_item.feeds.assetsdir');

        $pattern = "$assetsDir/$hash.*";
        $files = glob($pattern);
        if (!$files) {
            return $this->addImageFromUrl($img, $url, $params, true, false, 'jpg');
        }
        $files = glob($pattern);
        $tmp = $files[0];

        $params['source']  = 'feed';
        $params['sourceurl'] = $img;
        $params['sourcepage'] = $url;
        $params['hash'] = $hash;

        $return = $this->addImage($tmp, $params, false, 'jpg');

        if ($return && $sourceDomain = $return->getSourceDomain()) {
            $repo = $this->dm->getRepository('PW\AssetBundle\Document\Source');
            $source = $repo->findOneByName($sourceDomain);

            if (!$source) {
                $source = new Source();
                $source->setName($sourceDomain);
            }

            $source->incAssetCount();
            $this->dm->persist($source);
            $this->dm->flush();
        }

        return $return;
    }

    /**
     * Save an uploaded file into our assets
     *
     * @param Request $request The Requet object
     * @param mixed   $form    Who knows
     * @param string  $field   The field name of the file
     *
     * @return \PW\AssetBundle\Document\Asset
     */
    public function addUpload(Request $request, $form, $field)
    {
        $file = $request->files->get($form);
        if (empty($file) || empty($file[$field])) {
            return false;
        }

        /* @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
        $file   = $file[$field];
        $source = 'upload';
        $url    = $file->getClientOriginalName();
        return $this->addImage($file->getRealPath(), compact('source', 'url'), $sync_now = true);
    }

    /**
     * Handle a form file upload
     *
     * @param UploadedFile $file to process
     *
     * @return \PW\AssetBundle\Document\Asset
     */
    public function addUploadedFile(UploadedFile $file, $sync_now = false, $custom_extension = false)
    {
        $source = 'upload';
        $url = $file->getClientOriginalName();
        return $this->addImage($file->getRealPath(), compact('source', 'url'), $sync_now, $custom_extension);
    }

    /**
     * Adds an image from the disk to the assets.
     *
     * @param string $path  to the image
     * @param array  $params  properties
     * @param bool   $sync_now  should we do asset:sync now (true) or queue job for later (false)?
     * @param string $custom_extension  if 'false' then extension will be detected, otherwise we will use this extension
     *
     * @return \PW\AssetBundle\Document\Asset
     */
    public function addImage($path, $params = array(), $sync_now = false, $custom_extension = false)
    {
        $repo = $this->dm->getRepository('PW\AssetBundle\Document\Asset');

        if (!file_exists($path)) {
            $path = dirname(dirname(dirname(dirname(__DIR__)))) . '/web' . $path;
        }

        if (empty($params['hash'])) {
            $params['hash'] = sha1_file($path);
        }
        $doc = $repo->findOneByHash($params['hash']);

        if ($doc) {
            $url = $doc->getUrl();
            $urlIsLocal = ($url[0] === '/');
            if (!$urlIsLocal) {
                return $doc;
            }
        }

        if (!$doc) {
            $doc = new Asset;
            $doc->setHash($params['hash']);
        }
        foreach ($params as $key => $value) {
            if (is_callable(array($doc, 'set' . $key))) {
                $doc->{'set' . $key}($value);
            }
        }

        $doc->setIsActive(true);
        $sourceUrl = $doc->getSourceUrl();
        if ($sourceUrl && preg_match('/\.\d{3,4}($|\?)/', $sourceUrl)) {
            $path_for_pathinfo = $sourceUrl;
        } else {
            $path_for_pathinfo = $path;
        }
        $pathInfo = pathinfo($path_for_pathinfo);
        
        $extension = self::EXT;
        if ($custom_extension && strlen($custom_extension)>2) {
            $extension = $custom_extension;
        }
        //$extension = strtolower(@$pathInfo['extension']);
        //if ($custom_extension && strlen($custom_extension)>2) {
        //    $extension = $custom_extension;
        //} else if (strpos($path_for_pathinfo, 'getty')===false && $this->isPngFile($path_for_pathinfo)) {
        //    $extension = '.png';
        //} else {
        //    $extension = self::EXT;
        //}
        if (substr($extension,0,1)!='.') {
            $extension = '.'.$extension;
        }
        if ($extension=='.png') {
            $doc->setAllowPng(true);
        }
        file_put_contents('/tmp/beantalkd-assets.log', 'final extension: '.$extension." (original: ".@$pathInfo['extension'].")\n", FILE_APPEND);
        $temp_path = dirname($path) . '/' . $params['hash'] . $extension;
        
        $imagine = new \Imagine\Imagick\Imagine();
        $image = $imagine->open($path);  
        if ($extension!='.png' && $this->isPngFile($path)) {
            // we convert png image to non png image, so we need to make transparent to white color
            $background = new \Imagine\Image\Color('#fff');
            $topLeft    = new \Imagine\Image\Point(0, 0);
            $canvas     = $imagine->create($image->getSize(), $background);

            $canvas
                ->paste($image, $topLeft)
                ->save($temp_path, array('quality' => self::JPEG_QUALITY))
            ;
        } else {
            $image->save($temp_path, array('quality' => self::JPEG_QUALITY));
        }
        
        $doc->setUrl($this->handler->copy($temp_path, $params['hash'] . $extension));        
        unlink($temp_path);
        
        if (!empty($params['type']) && $params['type'] !== 'user') {
            $doc->setType($params['type']);
            $this->dm->persist($doc);
            $this->dm->flush();
        } else {
            $this->dm->persist($doc);
            $this->dm->flush();

            /*$this->event->publish(
                'asset.create',
                array(
                    'assetId' => $doc->getId()
                ),
                'high',
                'assets',
                'feeds' //server
            );*/
        }
        
        $doc = $this->syncAsset($doc, $sync_now);
        return $doc;
    }
    
    /**
     * Sync an Asset
     *
     * @param Asset $asset     Full file path
     * @param bool  $sync_now  should we do asset:sync now (true) or queue job for later (false)?
     * @return Asset $asset - same or updated (if $sync_now) asset
     */ 
    public function syncAsset($asset, $sync_now = false)
    {
        $dir = '..';
        if ($this->host == 'sparkrebel.com') {
            $dir = '/var/www/sparkrebel.com/current';
        } else if ($this->host == 'staging.sparkrebel.com') {
            $dir = '/var/www/staging.sparkrebel.com/current';
        }
        if ($sync_now) {
            // do asset:sync now:
            $command = "cd $dir && php app/console asset:sync {$asset->getId()} --verbose --env=prod >> app/logs/addController_syncAsset.log 2>&1";
            system($command);
            $this->dm->detach($asset);
            $asset = $this->dm->getRepository('PWAssetBundle:Asset')->find($asset->getId());
        }  else {
            // request asset:sync job on feeds server ('assets' tube):
            if ($dir != '..') {
                // for web1,feeds1,staging servers:
                $command = "cd $dir && php app/console leezy:pheanstalk:put assets '\"asset:sync ".$asset->getId()." --verbose --env=prod\"' high 0 0 feeds --env=prod";
                system($command);
            } else {
                $command = "asset:sync ".$asset->getId()." --verbose --env=prod";
                $this->event->requestJob($command, 'high', 'assets', '', 'feeds');
            }  
        }
        return $asset;
    }

    /**
     * Add an image from a url
     *
     * @param string  $img     url to the image
     * @param string  $url     url to the page the image is on
     * @param array   $params  other fields passed to addImage
     * @param boolean $refresh ignore checking if we've already downloaded the file
     * @param bool    $sync_now  should we do asset:sync now (true) or queue job for later (false)?
     * @param string  $custom_extension  if 'false' then extension will be detected, otherwise we will use this extension
     *
     * @return created or found asset instance
     */
    public function addImageFromUrl($img, $url = '', $params = array(), $refresh = false, $sync_now = false, $custom_extension = false)
    {
        if (!$refresh) {
            $repo = $this->dm->getRepository('PW\AssetBundle\Document\Asset');
            $doc = $repo->findOneBySourceUrl($img);

            if ($doc) {
                return $doc;
            }
        }

        $tmp = tempnam('/tmp', 'upload_');
        if (!@copy($img, $tmp)) {
            return false;
        }

        if (empty($params['source'])) {
          $params['source']  = 'site';
        }
        $params['sourceUrl'] = $img;
        $params['sourcePage'] = $url;

        $return = $this->addImage($tmp, $params, $sync_now, $custom_extension);
        unlink($tmp);

        if ($return && $sourceDomain = $return->getSourceDomain()) {
            $repo = $this->dm->getRepository('PW\AssetBundle\Document\Source');
            $source = $repo->findOneByName($sourceDomain);

            if (!$source) {
                $source = new Source();
                $source->setName($sourceDomain);
            }
            if($refresh === false) {
                $source->incAssetCount();
            }
            $this->dm->persist($source);
            $this->dm->flush();
        }

        return $return;
    }
    
    /**
     * used to adding assets from vide sites
     *
     * @param string $url 
     * @param string $params 
     * @return Asset
     * @author Michał Dąbrowski
     */
    public function addVideoFromUrl($url, $params = array())
    {
        $repo = $this->dm->getRepository('PW\AssetBundle\Document\Asset');
        $doc = $repo->findOneBySourceUrl($url);
        if($doc) {
            return $doc;
        }
        $sparker = new \PW\PostBundle\Model\VideoSparker($url);
        
        $doc = new Asset;
        $doc->setSourceUrl($url);
        $doc->setSourcePage($url);
        $doc->setSource('video_site');
        $doc->setHost($sparker->getOriginalHost());
        $doc->setHash(sha1($url));
        $doc->setVideoCode($sparker->getVideoCode());
        if (!empty($params['type']) && $params['type'] !== 'user') {
            $doc->setType($params['type']);
        }            
        $doc->setisActive(true);

        $img = $sparker->getBigThumbnailUrl();
        $tmp = tempnam('/tmp', 'upload_');
        if (!@copy($img, $tmp)) {
            return false;
        }
        
        $doc->setUrl($this->handler->copy($tmp, $doc->getHash() . self::EXT));

        $this->dm->persist($doc);
        $this->dm->flush();
        
        
        $repo = $this->dm->getRepository('PW\AssetBundle\Document\Source');
        $source = $repo->findOneByName($sparker->getOriginalHost());

        if (!$source) {
            $source = new Source();
            $source->setName($sparker->getOriginalHost());
        }

        $source->incAssetCount();
        $this->dm->persist($source);
        $this->dm->flush();
        
        $this->event->publish(
           'asset.create',
           array(
               'assetId' => $doc->getId()
           ),
           'high',
           'assets',
           'feeds' //server
        );
        
        return $doc;
    }
    
    /**
     * Called automatically
     *
     * @param Object $dm Document manager instance
     */
    public function setDocumentManager(\Doctrine\ODM\MongoDB\DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function getDocumentManager()
    {
        return $this->dm;
    }

    /**
     * Called automatically
     *
     * @param Object $eventManager instance
     */
    public function setEventManager(\PW\ApplicationBundle\Model\EventManager $eventManager = null)
    {
        $this->event = $eventManager;
    }
    
    /**
     * Called automatically
     *
     * @param string $host from parameters.yml
     */
    public function setHost($host)
    {
        $this->host = $host;
    }
    
    /**
     * Check if a file is a PNG file. Does not depend on the file's extension
     *
     * @param string $filename Full file path
     * @return boolean|null
     */ 
    public function isPngFile($filename)
    {
        // check if the file exists
        if (!file_exists($filename)) {
            return null;
        }
     
        // define the array of first 8 png bytes
        $png_header = array(137, 80, 78, 71, 13, 10, 26, 10);
        // or: array(0x89, 0x50, 0x4E, 0x47, 0x0D, 0x0A, 0x1A, 0x0A);
     
        // open file for reading
        $f = fopen($filename, 'r');
     
        // read first 8 bytes from the file and close the resource
        $header = fread($f, 8);
        fclose($f);
     
        // convert the string to an array
        $chars = preg_split('//', $header, -1, PREG_SPLIT_NO_EMPTY);
     
        // convert each charater to its ascii value
        $chars = array_map('ord', $chars);
     
        // return true if there are no differences or false otherwise
        return (count(array_diff($png_header, $chars)) === 0);
    }
}
