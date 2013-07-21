<?php

namespace PW\NewsletterBundle\Extension;

use PW\NewsletterBundle\TokenParser\Cache as TokenParserCache;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Fragmented Template Caching
 */
class Cache extends \Twig_Extension
{
    protected $expireAfter;
    protected $cachePrefix;
    protected $enabled;
    protected $container;
    protected $dir;

    /**
     * Constructor with optional params to set cache generation and default cache expiry
     *
     * @param $cachePrefix Sets generation id on all cache keys 
     * @param $expireAfter Sets default expireAfter to one day
     */
    public function __construct(ContainerInterface $container, $cachePrefix = 0, $expireAfter = 86400)
    {
        $this->expireAfter = $expireAfter;
        $this->cachePrefix = $cachePrefix;
        $this->container = $container;
        $this->dir = $container->getParameter('kernel.cache_dir') . '/twig-fragment-caching';
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0777, true);    
        }
        
        $this->enabled = is_writable($this->dir);
    }

    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return array An array of Twig_TokenParserInterface or Twig_TokenParserBrokerInterface instances
     */
    public function getTokenParsers()
    {
        return array(new TokenParserCache());
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'cache';
    }

    /**
     * Returns wether the cache is enabled, check if APC extension is loaded and enabled
     *
     * @return string The extension name
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Returns cache key
     *
     * @param $cacheKey Cache key given from the cache block
     */
    protected function generateCacheKey($cacheKey)
    {
        return 'twig_cache_' . $this->cachePrefix . '_' . $cacheKey;
    }

    /**
     * Returns cache key
     *
     * @param $cacheKey Cache key given from the cache block
     * @return boolean Wether a cache exists for sent cache key
     */
    public function cacheExists($cacheKey)
    {
        if ($this->isEnabled()) {
            $fileExist = file_exists($this->getCachePath($cacheKey));

            if($fileExist && (filemtime($this->getCachePath($cacheKey)) + $this->expireAfter) > time()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns cached content
     *
     * @param $cacheKey Cache key given from the cache block
     * @return string The cached content
     */
    public function cacheGet($cacheKey)
    {
        if ($this->isEnabled()) {
            return file_get_contents($this->getCachePath($cacheKey));
        }

        return false;
    }

    /**
     * Sets the content to cache
     *
     * @param $cacheKey Cache key given from the cache block
     */
    public function cacheSet($cacheKey, $body)
    {
        if ($this->isEnabled()) {
            file_put_contents($this->getCachePath($cacheKey), $body);
        }
    }

    /**
     * Gets the temp file path
     *
     * @param $cacheKey
     * @return string
     */
    protected function getCachePath($cacheKey)
    {
        return $this->dir . '/'. $this->generateCacheKey($cacheKey);
    }


}
