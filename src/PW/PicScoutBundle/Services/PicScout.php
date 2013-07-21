<?php

namespace PW\PicScoutBundle\Services;

use PW\AssetBundle\Document\Asset;
use PW\AssetBundle\Provider\AssetProvider;
use PW\PostBundle\Model\PostManager;
use PW\PicScoutBundle\Mailer\Mailer;
use PW\CelebBundle\GettyImage;
use PW\AssetBundle\Extension\AssetUrl;
/**
 * Service to check assets via getty and picscout api
 **/
class PicScout
{
    private $domain;
    private $apiKey;
    private $buzz;
    private $mailer;
    private $dm;
    public $au;

    public function __construct($domain, $apiKey, \Buzz\Browser $buzz, PostManager $postManager)
    {
        $this->domain = $domain;
        $this->apiKey = $apiKey;
        $this->buzz = $buzz;
        $this->postManager = $postManager;        
        $this->dm = $this->postManager->getDocumentManager();
        $this->buzz->getClient()->setTimeout(15);
        $this->au = new AssetUrl;
    }   


    /**
     * Checks if we can use sparked imaged. True if we can, false if we cant
     *
     * return boolean
     **/
    
    public function check(Asset $asset)
    {        
        $url = $this->getPicscoutUrl($asset);
        $content = $this->callPicscout($url);
    	
    	if(isset($content['ids'][0])) { //check if we had reponse from picscout
    		$img_id = $content['ids'][0];
            
    		$getty_image_img =  $this->getGettyImageId($img_id);        
            if(!is_null($getty_image_img)) {
                
                $g = new GettyImage;
                $rs = $g->getImageDetails($getty_image_img);
                if($rs !== null) {                    
                    if(empty($rs->Images) === false) {                                               
                        $getty_data['imageId'] = $getty_image_img;
                        $getty_data['picScoutImageId'] = $img_id;

                        $image = $rs->Images[0];
                        $meta =  array(
                            'artist' => $image->Artist,
                            'copyright' => $image->Artist . ' / ' . $image->CollectionName
                        );                       
                        $asset->setMeta($meta);
                        $asset->setFromGetty(true);      
                        $asset->setGettyData($getty_data);
                        $this->dm->persist($asset);
                        $this->dm->flush();                  
                    }
                }
            }

    	}
        
    	//no ids was found, we can use asset
    	return true;
    }

    public function getPicscoutUrl(Asset $asset)
    {        
        $image_url = $this->au->version($asset, 'full', true);

        return "https://{$this->domain}/v1/search?key={$this->apiKey}&url={$image_url}";    
    }


    public function callPicscout($url)
    {
        $response = $this->buzz->get($url);     
        $content = json_decode($response->getContent(), true);
        
        $getty_data = array();
        
        while(isset($content["timeToQuery"])) {
            $wait_ms = $content["timeToQuery"] + 1000; //add second to be sure
            sleep($wait_ms / 1000);
            $response = $this->buzz->get($url);
            $content = json_decode($response->getContent(), true);                      
        }
        return $content;
    }

    public function getGettyImageId($img_id)
    {
    	$url = "https://{$this->domain}/v1/images/{$img_id}?key={$this->apiKey}";    		
    	$response = $this->buzz->get($url);    	
    	$content = json_decode($response->getContent(), true);   
        

        $found = in_array(
            strtolower($content['imageDetails']['licensingInfo'][0]['name']), 
            array(
                strtolower('Getty Images'),
                strtolower('WireImage'),
                strtolower('Filmagic'),
                strtolower('FilmMagic'),
                strtolower('Agence France-Presse'),
                strtolower('Agence France Presse'),
                strtolower('AFP')

            )
        );
        if ($found) {        	
    	   return $content['imageDetails']['licensingInfo'][0]["imageId"];
        } else {
            return null;
        }
    }
    

}
