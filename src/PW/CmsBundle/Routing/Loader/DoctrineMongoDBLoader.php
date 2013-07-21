<?php

namespace PW\CmsBundle\Routing\Loader;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use PW\CmsBundle\Model\PageManager;

class DoctrineMongoDBLoader implements LoaderInterface
{
    private $loaded = false;

    /**
     * @var \PW\CmsBundle\Model\PageManager
     */
    protected $pageManager;

    /**
     * @var string
     */
    protected $pageController;

    /**
     * @param PageManager $pageManager
     * @param string $pageController;
     */
    public function __construct(PageManager $pageManager, $pageController)
    {
        $this->pageManager    = $pageManager;
        $this->pageController = $pageController;
    }

    /**
     * Loads a resource.
     *
     * @param mixed  $resource The resource
     * @param string $type     The resource type
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Attempting to load DoctrineMongoDB Routing loader twice.');
        }

        $collection = new RouteCollection();

        try {
            $pages = $this->pageManager->findAllActive();
            foreach ($pages as $page /* @var $page \PW\CmsBundle\Document\Page */) {
                $collection->add('pw_cms_' . $page->getSlug(), $page->getRoute($this->pageController));
            }
            $this->loaded = true;
        } catch (\MongoConnectionException $e) {
            // We can't connect to MongoDB?
            // Ignore exception so we don't break unrelated things
        } catch (\MongoCursorException $e) {
            // Also ignore any query errors...
        }

        return $collection;
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return Boolean true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return 'doctrine_mongodb' === $type;
    }

    /**
     * Gets the loader resolver.
     *
     * @return LoaderResolver A LoaderResolver instance
     */
    public function getResolver()
    {
    }

    /**
     * Sets the loader resolver.
     *
     * @param LoaderResolver $resolver A LoaderResolver instance
     */
    public function setResolver(LoaderResolverInterface $resolver)
    {
    }
}
