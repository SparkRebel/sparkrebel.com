<?php

namespace PW\AssetBundle\Extension;

use Doctrine\ODM\MongoDB\DocumentManager;
use PW\ApplicationBundle\Model\EventManager;


/**
 * AssetUrl
 */
class AssetUrl extends \Twig_Extension
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;
    
    protected $eventManager = null;

    /**
     * Called automatically
     *
     * @param Object $dm Document manager instance
     */
    public function setDocumentManager(DocumentManager $dm)
    {
        $this->dm = $dm;
    }
    
    public function setEventManager(EventManager $m)
    {
        $this->eventManager = $m;
    }

    /**
     * getFilters
     *
     * @return array of filters to apply
     */
    public function getFilters()
    {
        return array(
            'version' => new \Twig_Filter_Method($this, 'version'),
        );
    }

    /**
     * version an image
     *
     * Can also accept a url (string)
     *
     * @param string $asset   original asset instance
     * @param string $version the version of the image to return
     *
     * @return the url for the specific version of this asset
     */
     public function version($asset, $version = 'medium', $ensureAbsoluteUrl = false, $requestAssetVersionIfMissing = true)
     {
         if (!$asset) {
             return 'http://sparkrebel.com/images/items/blank.png';
         }

         if (is_object($asset)) {
             if ($asset->getDeleted()) {
                 return 'http://sparkrebel.com/images/removed.png';
             }
             $url = $asset->getUrl();
             $thumbsExtension = $asset->getThumbsExtension();
         } else {
             $url = preg_replace('@\.\w\.@', '.', $asset);
             $thumbsExtension = 'jpg';
         }

         if (strpos($url, 'i.plumwillow.com') !== false) {
             $url = $this->useAssetHosts($url);
         } elseif ($ensureAbsoluteUrl) {
             // we want absolute url         
             if (substr($url,0,4)!='http') {
                 // this is relative url, so we need to make sure it is absolute

                if (php_sapi_name() === 'cli') {
                    return 'http://sparkrebel.com' . $asset->getUrl();                                        
                } else {
                    if (substr($url,0,1)!='/') $url .= '/'.$url; // ensure slash on the beginning         
                    $url = $this->getLocalVersionUrl($url, $version, true, $asset);
                    $url = 'http://'.$_SERVER['HTTP_HOST'].$url; // make absolute url
                    return $url; 
                }                 
             }
         }

         // check if url is remote
         if (strpos($url, '://') === false) {
            // its not remote
            return $this->getLocalVersionUrl($url, $version, true, $asset);
         }

         $pos = strrpos($url, '.');
         $remoteVersionUrl = substr($url, 0, $pos) . '.' . $version[0] . '.' . $thumbsExtension;
         return $remoteVersionUrl;
     }

    /**
     * getName
     *
     * @return string
     */
    public function getName()
    {
        return 'pw_asset_version';
    }
    
    /**
     * Return either local version url ( for example [hash].l.png ) or original local image url
     *
     * @param string $localRelativeUrl - in web dir, must start with /
     *
     * @return boolean
     */
    public function getLocalVersionUrl($localRelativeUrl, $version='medium', $requestAssetVersionIfMissing = true, $asset = null)
    {
        $thumbsExtension = 'jpg';
        if ($asset) {
            $thumbsExtension = $asset->getThumbsExtension();
        }
        $pos = strrpos($localRelativeUrl, '.');
        $version_url = substr($localRelativeUrl, 0, $pos) . '.' . $version[0] . '.' .$thumbsExtension;
        $path = dirname(dirname(dirname(dirname(__DIR__)))) . '/web' . $version_url;
        if (file_exists($path)) {
            return $version_url;
        } else { 
            // image version dont exists
            if ($requestAssetVersionIfMissing && $asset && !empty($asset) && $this->eventManager && !empty($this->eventManager)) {            
                if (isset($_GET['allow_reprocessing_of_assets'])) { //&& $this->eventManager->getMode() !== 'foreground'
                    $localPath = dirname(dirname(dirname(dirname(__DIR__)))) . '/web' . $localRelativeUrl;
                    //if (file_exists($localPath)) {  // check also if original image exists -> if not then we will be requesting asset:version forever...
                        $interval = new \DateInterval("PT60M");
                        $date = $asset->getModified();
                        $date->add($interval);
                        if ($date->getTimestamp() < time()) {
                            // update Asset "modified" field to current time
                            $asset->setModified(new \DateTime());
                            $this->dm->persist($asset);
                            $this->dm->flush();
                            // queue asset:version job to create missing versions
                            $this->eventManager->requestJob('asset:sync ' . escapeshellarg($asset->getId()), 'medium', 'assets', '', 'feeds');
                        } 
                    //} else {
                    //    // original image doesnt exists -> return blank.png
                    //    return '/images/items/blank.png';
                    //}
                }
            }
            // use original image for now
            return $localRelativeUrl;
        }
    }
    
    /**
     * Check if local version of image exists
     *
     * @param string $localRelativeUrl - in web dir, must start with /
     *
     * @return boolean
     */
    public function hasLocalVersionUrl($localRelativeUrl, $version='medium')
    { 
        $localVersionUrl = $this->getLocalVersionUrl($localRelativeUrl, $version, false);
        return $localVersionUrl!=$localRelativeUrl;
    }
    
    /**
     * useAssetHosts
     *
     * we are storing i.plumwillow.com in the url field after uploading assets - as that's the name
     * of the s3 bucket. Based on the first char of the asset filename (which is based on the hash)
     * select a consistent host to use for retriving the asset and rewrite to use assets?.sparkrebel.com
     *
     * @param string $url the stored url
     *
     * @return the url on our own assets\d hosts
     */
    protected function useAssetHosts($url)
    {
        preg_match('@http://([^/]*).+/(.)[^/]*$@', $url, $match);
        if ($match) {
            $firstChar = strtolower($match[2]);
            if ($firstChar > 'a') {
                $host = 'assets3.sparkrebel.com';
            } elseif ($firstChar > '4') {
                $host = 'assets2.sparkrebel.com';
            } else {
                $host = 'assets1.sparkrebel.com';
            }

            $url = str_replace($match[1], $host, $url);
        }

        return $url;
    }

}
