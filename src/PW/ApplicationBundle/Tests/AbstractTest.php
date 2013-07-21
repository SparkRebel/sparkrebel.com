<?php

namespace PW\ApplicationBundle\Tests;

use Doctrine\Common\DataFixtures\Executor\MongoDBExecutor as Executor,
    Doctrine\Common\DataFixtures\Purger\MongoDBPurger as Purger,
    Doctrine\Common\DataFixtures\Loader,
    Doctrine\Common\DataFixtures\ReferenceRepository,
    Symfony\Bundle\FrameworkBundle\Test\WebTestCase,
    Symfony\Bundle\FrameworkBundle\Console\Application,
    Symfony\Component\Console\Input\ArrayInput,
    Symfony\Component\Console\Output\StreamOutput;

/**
 * Abstract document test class
 *
 * We don't want to keep repeating how to load _fixtures - so we won't
 */
abstract class AbstractTest extends WebTestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var int
     */
    protected $_maxMemory = 5242880;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $_dm;

    /**
     * Array of _fixtures to load.
     * @var array
     */
    protected $_fixtures = array();

    /**
     * @var bool
     */
    protected $_fixturesAppend = false;

    /**
     * Called in setUp before fixtures are loaded
     */
    public function init()
    {
    }

    /**
     * Setup our test environment
     */
    public function setUp()
    {
        $kernel = static::createKernel(array('environment' => 'test', 'debug' => false));
        $kernel->boot();
        $this->container = $kernel->getContainer();
        $this->_dm = $this->container->get('doctrine_mongodb.odm.document_manager');

        // For any pre-fixtures setup
        $this->init();

        if ($this->_fixtures) {
            $this->_loadFixtures($this->_fixtures, $this->_fixturesAppend);
        }

        $this->container->get('snc_redis.default')->flushdb();
        $this->container->get('pw.event')->resetRequests();
    }

    /**
     * _loadFixtures
     *
     * @param array   $_fixtures names of _fixtures to load
     * @param boolean $append    append data, or replace?
     */
    protected function _loadFixtures($_fixtures = array(), $append = true)
    {
        $defaultFixtures = $feedsFixtures = false;

        $loader = new Loader();
        $refRepo = new ReferenceRepository($this->_dm);

        foreach ((array) $_fixtures as $name) {

            if (!strpos($name, '\\')) {
                $className = get_class($this);
                preg_match('@\W(.*)Bundle@', $className, $match);
                $bundle = $match[1];
                $name = "PW\\{$bundle}Bundle\DataFixtures\MongoDB\\{$name}";
            }

            $fixture = new $name();

            if (strpos($name, 'FeedItem')) {
                $feedsFixtures = true;
                if (empty($this->feedsDm)) {
                    $this->feedsDm = $this->container->get('doctrine.odm.mongodb.feeds_document_manager');
                }

                if (empty($feedsLoader)) {
                    $feedsLoader = new Loader();
                    $feedsRepo = new ReferenceRepository($this->feedsDm);
                }

                $fixture->setReferenceRepository($feedsRepo);
                $feedsLoader->addFixture($fixture);
            } else {
                $defaultFixtures = true;
                $fixture->setReferenceRepository($refRepo);
                $loader->addFixture($fixture);
            }
        }

        if ($defaultFixtures) {
            $purger = new Purger();
            $executor = new Executor($this->_dm, $purger);
            $executor->execute($loader->getFixtures(), $append);
        }

        if ($feedsFixtures) {
            $feedsPurger = new Purger();
            $feedsExecutor = new Executor($this->feedsDm, $feedsPurger);
            $feedsExecutor->execute($feedsLoader->getFixtures(), $append);
        }
    }

    /**
     * Builds up the environment to run the given command.
     *
     * @param string $name   of the command to test
     * @param array  $params to pass to the command
     *
     * @link https://github.com/liip/LiipFunctionalTestBundle/blob/master/Test/WebTestCase.php#L82
     * @return string
     */
    protected function runCommand($name, array $params = array())
    {
        array_unshift($params, $name);

        $kernel = $this->createKernel(array('environment' => 'test', 'debug' => false));
        $kernel->boot();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput($params);
        $input->setInteractive(false);

        $fp = fopen('php://temp/maxmemory:'.$this->_maxMemory, 'r+');
        $output = new StreamOutput($fp);

        $application->run($input, $output);

        rewind($fp);
        return stream_get_contents($fp);
    }

    /**
     * Verifies that an event was triggered.
     *
     * @param string $event  name of the event
     * @param array  $params expected arguments
     */
    public function assertEventTriggered($event, array $params = array())
    {
        $requests = $this->container->get('pw.event')->getRequests();
        $this->assertTrue(
            isset($requests['publish']),
            'No Events have been published'
        );

        $eventFound  = false;
        $paramsMatch = false;
        $events = $requests['publish'];
        foreach ($events as $eventInfo) {
            if ($eventInfo['event'] == $event) {
                $eventFound = true;
                if (!empty($params)) {
                    $diff = array_diff_assoc($params, $eventInfo['message']);
                    if (empty($diff)) {
                        $paramsMatch = true;
                        break;
                    }
                } else {
                    break;
                }
            }
        }

        $this->assertTrue($eventFound, "Event $event not triggered");
        if (!empty($params)) {
            $this->assertTrue($paramsMatch);
        }
    }

    /**
     * Shuts the kernel down if it was used in the test.
     */
    protected function tearDown()
    {
        parent::tearDown();
        unset($GLOBALS);
    }
}
