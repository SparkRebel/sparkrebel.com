<?php

namespace PW\GettyImagesBundle\Services;

use Buzz\Browser;
use Buzz\Client\ClientInterface;
use Buzz\Message\Factory\FactoryInterface;
use Buzz\Message\RequestInterface;
use Symfony\Component\Config\ConfigCache;

class Connect extends Browser
{
    private $options = array(
        'base_url'        => '',
        'version'         => '',
        'system_id'       => null,
        'system_password' => null,
        'user_name'       => null,
        'user_password'   => null,
        'cache_dir'       => false,
        'debug'           => false,
    );

    private $session = array(
        'token'     => null,
        'stoken'    => null,
        'timestamp' => null,
        'mins_left' => 0,
    );

    private $endpoints = array(
        'CreateSession'                  => 'session',
        'RenewSession'                   => 'session',
        'SearchForImages'                => 'search',
        'GetImageDetails'                => 'search',
        'GetEvents'                      => 'search',
        'GetImageDownloadAuthorizations' => 'download',
    );

    public function __construct(ClientInterface $client = null, FactoryInterface $factory = null, array $options = array())
    {
        parent::__construct($client, $factory);

        if ($diff = array_diff(array_keys($options), array_keys($this->options))) {
            throw new \InvalidArgumentException(sprintf('Getty Connect API does not support the following options: \'%s\'.', implode('\', \'', $diff)));
        }

        $this->options = array_merge($this->options, $options);
    }

    public function initialize()
    {
        $session = $this->session;
        if (!empty($session['token']) && $session['mins_left'] > 0) {
            return;
        }

        $cacheDir = $this->options['cache_dir'];
        $systemId = $this->options['system_id'];
        $userName = $this->options['user_name'];
        $debug    = $this->options['debug'];
        $cache    = new ConfigCache("{$cacheDir}/{$systemId}_{$userName}.php", $debug);
        if (!$cache->isFresh()) {
            $this->createSession();
            $cache->write('<?php return ' . var_export($this->session, true) . ';');
            return;
        }

        $session = include $cache;
        if ($this->isSessionExpired()) {
            $this->renewSession();
            $cache->write('<?php return ' . var_export($this->session, true) . ';');
            return;
        }

        $this->session = $session;
    }

    public function createSession()
    {
        $method = 'CreateSession';
        $content = array(
            'RequestHeader' => array(
                'Token'          => '',
                'CoordinationId' => '',
            ),

        );

        $response = $this->sendRequest($method, $content);
        if (!$response->isSuccessful()) {
            throw new \Exception('Getty Image API Error');
        }

        $result = $response->getResponseResult();
        $this->session = array(
            'token'     => $result['Token'],
            'stoken'    => $result['SecureToken'],
            'timestamp' => time(),
            'mins_left' => (int) $result['TokenDurationMinutes'],
        );

        return $response;
    }

    public function renewSession()
    {
        $method = 'RenewSession';
        $content = array(
            'RequestHeader' => array(
                'Token'          => '',
                'CoordinationId' => '',
            ),
            "{$method}RequestBody" => array(
                'SystemId'       => $this->options['system_id'],
                'SystemPassword' => $this->options['system_password'],
            )
        );

        $response = $this->sendRequest($method, $content);
        if (!$response->isSuccessful()) {
            throw new \Exception('Getty Image API Error');
        }

        $result = $response->getResponseResult();
        $this->session = array(
            'token'     => $result['Token'],
            'stoken'    => $result['SecureToken'],
            'timestamp' => time(),
            'mins_left' => (int) $result['TokenDurationMinutes'],
        );

        return $response;
    }

    public function searchForImages($filter = array(), $query = array(), $options = array(), $language = null)
    {
        $method = 'SearchForImages';

        $defaultOptions = array(
            'IncludeKeywords' => true,
            'ItemCount' => '',
            'ItemStartNumber' => '',
            'RefinementOptionsSet' => '',
            'EditorialSortOrder' => '',
        );

        $content = array(
            'Filter'        => $filter,
            'Query'         => $query,
            'Language'      => $language,
            'ResultOptions' => $options,
        );
    }

    public function getToken()
    {
        if (!empty($this->session['token'])) {
            return $this->session['token'];
        }

        $this->initialize();
        return $this->session['token'];
    }

    /**
     * @param  string $method
     * @param  array  $content
     * @return \PW\GettyImagesBundle\Services\Connect\Message\Response
     */
    private function sendRequest($method, array $content = array())
    {
        $url = $this->getEndpoint($method);

        if (!isset($content["{$method}RequestBody"])) {
            $content = array(
                "{$method}RequestBody" => $content,
            );
        }

        if (!isset($content['RequestHeader'])) {
            $content['RequestHeader'] = array(
                'Token'          => $this->getToken(),
                'CoordinationId' => '',
            );
        }

        return $this->post($url, array(), $content);
    }

    /**
     * @param  string $method
     * @return string
     */
    private function getEndpoint($method)
    {
        $method = ucfirst($method);
        if (!isset($this->endpoints[$method])) {
            throw new \Exception("Invalid endpoint method: {$method}");
        }
        $baseUrl = $this->options['base_url'];
        $version = $this->options['version'];
        $type    = $this->endpoints[$method];

    }



    private function isSessionExpired($session = null)
    {
        if ($session === null) {
            $session = $this->session;
        }

        $minutesLeft = $session['mins_left'];
        if ($minutesLeft <= 0) {
            return true;
        }

        $timestamp   = $session['timestamp'];
        $minutesUsed = ceil((time() - $timestamp) / 60);
        if (empty($timestamp) || $minutesUsed >= 30) {
            return true;
        }

        return false;
    }
}
