<?php

namespace PW\CelebBundle;

/**
 *  Simple GettyImage class (requires curl)
 *
 *  @author crodas
 */
class GettyImage
{
    /** @fixme I should be in a config */
    protected $systemId = "10054";
    protected $systemPassword = "mPZo1omdXq4o0AJDlwWLEtzPzaXRHOiZm5ovIh3reII=";
    protected $userName = "sparkrebel_api";
    protected $userPassword = "1FWmcZ5jlIoJDCl";
    protected $sessionCache = "/tmp/getty.json";
    protected $token;
    protected $stoken;
    protected $expires;
    protected $debugOutput = false;

    public function __construct() 
    {
        if (!empty($this->sessionCache) && is_readable($this->sessionCache)) {
            $obj = unserialize(file_get_contents($this->sessionCache));
            if (!is_object($obj) || empty($obj->sessionCache)) return;
            $ttl = time() - filemtime($this->sessionCache) < 60 * $obj->CreateSessionResult->TokenDurationMinutes;
            if ($ttl && !empty($obj) && !empty($obj->CreateSessionResult)) {
	            $this->token   = $obj->CreateSessionResult->Token;
	            $this->stoken  = $obj->CreateSessionResult->SecureToken;
                $this->expires = filemtime($this->sessionCache) +(60*$obj->CreateSessionResult->TokenDurationMinutes);
            }
        }
    }

    protected function merge(\stdClass $a, \stdClass $b)
    {
        foreach ($b as $key => $value) {
            $a->$key = $value;
        }
    }
    
    /**
     *  Should var_dump json requests and responses?
     */    
    public function setDebugOutput($debugOutput = false)
    {
        $this->debugOutput = $debugOutput;
    }

    /**
     *  Peforms an HTTP POST request to an endpoint using
     *  GettyImage's format
     *
     *  @fixme I should be checking for errors
     *
     *  @return object
     */
    public function Query($url, Array $data)
    {
        if (empty($data['RequestHeader']) && !empty($this->token) && $this->expires > time()+10) {
            $data["RequestHeader"] = array (
        		"Token" => $this->token,
        	);
        } else if (empty($data['RequestHeader'])) {
            $this->Login();
            return $this->Query($url, $data);
        }
        $json = json_encode($data);
        if ($this->debugOutput) {
            echo "\njson_request: ";
            print_r($json);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: application/json')); 

        $result = curl_exec($ch);
        if ($this->debugOutput) {
            echo "\njson_response: ";
            print_r($result);
        }
	    return json_decode($result); 
    }

    /**
     *  Peforms a login and setup the token
     *
     *  @return void
     */
    public function Login()
    {
        $body = array(
	        "RequestHeader" => array(
		        "Token" => "",
		        "CoordinationId" => ""
	        ),
	        "CreateSessionRequestBody" => array(
		        "SystemId" => $this->systemId,
		        "SystemPassword" => $this->systemPassword,
		        "UserName" => $this->userName,
		        "UserPassword" => $this->userPassword,
		        "RememberedUser" => "true",
	        )
        );
        $endpoint = "https://connect.gettyimages.com/v1/session/CreateSession";
        $response = $this->Query($endpoint, $body);
        if (!empty($this->sessionCache)) {
            file_put_contents($this->sessionCache, serialize($response));
        }
	    $this->token   = $response->CreateSessionResult->Token;
	    $this->stoken  = $response->CreateSessionResult->SecureToken;
        $this->expires = $response->CreateSessionResult->TokenDurationMinutes*60 + time();
    }

    /**
     *  Performs a search and return the images result.
     *
     *  @return array
     */
    public function Search($phrase, $limit = 25, $offset = 1, $getDetails = true, $people = false)
    {
        $endpoint = "http://connect.gettyimages.com/v1/search/SearchForImages";
        $body = array (
        	"SearchForImages2RequestBody" => array (
         		"Query" => array (
        			"SearchPhrase" => $phrase,
                ),
                "Filter" => array(
                    "ImageFamilies" => array("editorial"),
         		),
         		"ResultOptions" => array (
        			"IncludeKeywords" => "true",
         			"ItemCount" => $limit,
         			"ItemStartNumber" => $offset,
         		)
        	)
        );

        if (!empty($people)) {
            $body['SearchForImages2RequestBody']['Query']['SpecificPersons'] = array($phrase);
        }

        $obj = $this->Query($endpoint, $body);
        if (!empty($obj->SearchForImagesResult)) {
            $tmp = $obj->SearchForImagesResult->Images;
            $ids = array();
            $images = array();
            foreach ($tmp as $image) {
                if ($getDetails) {
                    $ids[] = $image->ImageId;
                }
                $images[$image->ImageId] = new GettyResult($image, $this);
            }

            $details  = $this->getImageDetails($ids);
            $download = $this->getDownloadDetails($ids);
            foreach (array('details', 'download') as $type) {
                if (!empty($$type->Images)) {
                    foreach ($$type->Images as $detail) {
                        $images[$detail->ImageId]->merge($detail);
                    }
                }
            }
            return $images;
        } 
        return array();
    }

    /**
     *  Get image (or images) details.
     *
     *  @param Array|String $imageId 
     *  @param String $country
     *
     *  @return object
     */
    public function getImageDetails($imageId, $country = "USA")
    {
        $endpoint = "http://connect.gettyimages.com/v1/search/GetImageDetails";
        $body = array(
            "GetImageDetailsRequestBody" => array (
			    "CountryCode" => $country,
 			    "ImageIds" => is_array($imageId) ? $imageId : array($imageId),
 		    ),
        );
        $obj = $this->Query($endpoint, $body);
        if (!empty($obj->GetImageDetailsResult)) {
            return $obj->GetImageDetailsResult;
        }
        return null;
    }

    /**
     *  Get the download links (largest image). 
     *
     *  @param $imageId Array of image ids to fetch
     *
     *  @return object
     */
    public function getDownloadDetails($imageId)
    {
        $images = is_array($imageId) ? $imageId : array($imageId);
        foreach ($images as &$image) {
            $image = array("ImageId" => $image);
        }

        $endpoint = "http://connect.gettyimages.com/v1/download/GetLargestImageDownloadAuthorizations";
        $body = array(
            "GetLargestImageDownloadAuthorizationsRequestBody" => array (
 			    "Images" => $images,
 		    ),
        );
        $obj = $this->Query($endpoint, $body);
        if (!empty($obj->GetLargestImageDownloadAuthorizationsResult)) {
            $tmp = $obj->GetLargestImageDownloadAuthorizationsResult->Images; 
            $images = array();
            $ids    = array();
            foreach ($tmp as $image) {
                $images[$image->ImageId] = $image;
                $ids[] = array("DownloadToken" => $image->Authorizations[0]->DownloadToken);
            }

            $endpoint = "https://connect.gettyimages.com/v1/download/CreateDownloadRequest";
            $body = array(
                "RequestHeader" => array (
        		    "Token" => $this->stoken,
        	    ),
		        "CreateDownloadRequestBody" => array(
	 		        "DownloadItems" => $ids,
                )
            );
            $zobj = $this->Query($endpoint, $body);
            if (!empty($zobj->CreateDownloadRequestResult)) {
                foreach ($zobj->CreateDownloadRequestResult->DownloadUrls as $image) {
                    $this->merge($images[$image->ImageId], $image);
                }
                $obj = new \stdClass;
                $obj->Images = $images;
                return $obj;
            }

            return $obj->GetLargestImageDownloadAuthorizationsResult;
        }
        return null;
    }
}

