<?php

namespace PW\ItemBundle\Tests\Command;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\ItemBundle\Document\FeedItem,
    Doctrine\Common\DataFixtures\Executor\MongoDBExecutor as Executor,
    Doctrine\Common\DataFixtures\Purger\MongoDBPurger as Purger,
    Doctrine\Common\DataFixtures\Loader,
    Doctrine\Common\DataFixtures\ReferenceRepository;

/**
 * FeedItemStep1Command
 *
 * Overriden methods for testing purposes only
 */
class FeedItemStep1Command2 extends \PW\ItemBundle\Command\FeedItemStep1Command
{

    /**
    * __construct
    *
    * Setup the repos used by the process function and set the container instance
    *
    * @param mixed $container instance
    */
    public function __construct($container)
    {
        $this->setContainer($container);
        $this->setupRepos();
    }

    /**
    * Visibility wrapper to allow testing
    *
    * @param mixed $id feed item instance or id
    *
    * @return processed item instance
    */
    public function testProcessItem($id)
    {
        return $this->processItem($id, true);
    }
}

/**
 * FeedItemStep2Command
 *
 * Overriden methods for testing purposes only
 */
class FeedItemStep2Command extends \PW\ItemBundle\Command\FeedItemStep2Command
{

    /**
     * __construct
     *
     * Setup the repos used by the process function and set the container instance
     *
     * @param mixed $container instance
     */
    public function __construct($container)
    {
        $this->setContainer($container);
        $this->setupRepos();
    }

    /**
     * Visibility wrapper to allow testing
     *
     * @param mixed $id feed item instance or id
     *
     * @return processed item instance
     */
    public function testProcessItem($id)
    {
        return $this->processItem($id, true);
    }
}

/**
 * FeedItemStep2CommandTest
 *
 * @group item
 * @group feed
 */
class FeedItemStep2CommandTest extends AbstractTest
{
    /**
     * repos used by this test
     */
    protected $repos = array();

    /**
     * fixtures to load before each test
     */
    protected $_fixtures = array(
        'LoadExampleFeedItemData',
        'LoadExampleFeedImageData',
        'LoadExampleWhitelistData',
        'PW\CategoryBundle\DataFixtures\MongoDB\LoadExampleData'
    );

    /**
     * setUp
     */
    public function setUp()
    {
        if (!is_dir('/var/feed/assets')) {
            $this->markTestSkipped('/var/feed/assets does not exist.');
        }

        parent::setUp();

        $this->step1 = new FeedItemStep1Command2($this->container);
        $this->command = new FeedItemStep2Command($this->container);

        $this->repos['board'] = $this->_dm->getRepository('PWBoardBundle:Board');
        $this->repos['brand'] = $this->_dm->getRepository('PWUserBundle:Brand');
        $this->repos['category'] = $this->_dm->getRepository('PWCategoryBundle:Category');
        $this->repos['item'] = $this->_dm->getRepository('PWItemBundle:Item');
        $this->repos['post'] = $this->_dm->getRepository('PWPostBundle:Post');
        $this->repos['user'] = $this->_dm->getRepository('PWUserBundle:User');
        $this->repos['merchant'] = $this->_dm->getRepository('PWUserBundle:Merchant');
    }

    /**
     * testSimple
     *
     * Starting from an empty db - import one fixtured feed item
     * This is an unapproved brand
     */
    public function testSimple()
    {
        $this->step1->testProcessItem('fixionalprovider-levi-501');
        $this->command->testProcessItem('fixionalprovider-levi-501');
    }
}
