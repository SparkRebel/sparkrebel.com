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
class FeedItemStep1Command extends \PW\ItemBundle\Command\FeedItemStep1Command
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
     * @param array $names a flat array
     *
     * @return array of Category instances
     */
    public function testGetCategories($names)
    {
        return $this->getCategories($names);
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
 * FeedItemStep1CommandTest
 */
class FeedItemStep1CommandTest extends AbstractTest
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
        'LoadExampleWhitelistData',
        'PW\CategoryBundle\DataFixtures\MongoDB\LoadExampleData'
    );

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();

        $this->command = new FeedItemStep1Command($this->container);

        $this->repos['board'] = $this->_dm->getRepository('PWBoardBundle:Board');
        $this->repos['brand'] = $this->_dm->getRepository('PWUserBundle:Brand');
        $this->repos['category'] = $this->_dm->getRepository('PWCategoryBundle:Category');
        $this->repos['item'] = $this->_dm->getRepository('PWItemBundle:Item');
        $this->repos['post'] = $this->_dm->getRepository('PWPostBundle:Post');
        $this->repos['user'] = $this->_dm->getRepository('PWUserBundle:User');
        $this->repos['merchant'] = $this->_dm->getRepository('PWUserBundle:Merchant');
    }

    /**
     * testGetCategories1Leaf
     */
    public function testGetCategories1Leaf()
    {
        $expected = 'channel 1.1';
        $return = $this->command->testGetCategories(array($expected));
        $this->assertSame(1, count($return));
        $this->assertSame($expected, $return[0]->getName());
    }

    /**
     * testGetCategories1Parent
     */
    public function testGetCategories1Parent()
    {
        $expected = 'channel 1';
        $return = $this->command->testGetCategories(array($expected));
        $this->assertSame(1, count($return));
        $this->assertSame($expected, $return[0]->getName());
    }

    /**
     * testGetCategoriesDropsParent
     */
    public function testGetCategoriesDropsParent()
    {
        $expected = 'channel 1.1';
        $return = $this->command->testGetCategories(array($expected, 'channel 1'));
        $this->assertSame(1, count($return));
        $this->assertSame($expected, $return[0]->getName());
    }

    /**
     * testGetCategoriesDropsAllParents
     */
    public function testGetCategoriesDropsAllParents()
    {
        $return = $this->command->testGetCategories(array(
            'channel 1',
            'channel 2',
            'channel 3.8',
            'channel 4.9',
            'channel 5.10',
        ));
        $this->assertSame(3, count($return));

        $this->assertSame('channel 3.8', $return[0]->getName());
        $this->assertSame('channel 4.9', $return[1]->getName());
        $this->assertSame('channel 5.10', $return[2]->getName());
    }

    /**
     * testSimple
     *
     * Starting from an empty db - import one fixtured feed item
     * This is an unapproved brand
     *
     * @param boolean $runCommand Whether to run the command or just the tests
     */
    public function testSimple($runCommand = true)
    {
        if ($runCommand) {
            $this->command->testProcessItem('fixionalprovider-levi-501');
        }

        $item = $this->repos['item']->findOneBy(array('feedId' => 'fixionalprovider-levi-501'));
        $this->assertSame(1, $this->repos['item']->findBy(array())->count());
        $this->assertNotNull($item);

        $this->assertSame(1, $this->repos['user']->findBy(array())->count());
        $this->assertSame(1, $this->repos['merchant']->findBy(array())->count());
        $this->assertNotNull($this->repos['merchant']->findOneBy(array('name' => 'Levi.com')));
        $this->assertSame(0, $this->repos['brand']->findBy(array())->count());
    }

    /**
     * testIdempotence
     *
     * It should be possible to rerun with absolutely no concequences
     * This is an unapproved brand
     */
    public function testIdempotence()
    {
        $this->command->testProcessItem('fixionalprovider-levi-501');
        $this->testSimple();
    }

    /**
     * An integration style test - run on the cli and see what happens
     *
     * This is an unapproved brand
     */
    public function testIntegration()
    {
        `php app/console --no-debug feed:item:step1 --env=test fixionalprovider-levi-501`;
        $this->testSimple(false);
    }

    /**
     * testSimpleApproved
     *
     * Starting from an empty db - import one fixtured feed item
     */
    public function testSimpleApproved()
    {
        $this->command->testProcessItem('fixionalprovider-thompson-wide-jeans');

        $this->assertSame(1, $this->repos['item']->findBy(array())->count());

        $item = $this->repos['item']->findOneBy(array('feedId' => 'fixionalprovider-thompson-wide-jeans'));
        $this->assertNotNull($item);
        $this->assertSame(false, $item->getIsActive());

        $this->assertSame(2, $this->repos['user']->findBy(array())->count());
        $this->assertSame(1, $this->repos['merchant']->findBy(array())->count());
        $this->assertNotNull($this->repos['merchant']->findOneBy(array('name' => 'Alloy.com')));
        $this->assertSame(1, $this->repos['brand']->findBy(array())->count());
        $this->assertNotNull($this->repos['brand']->findOneBy(array('name' => 'Thompson')));
    }

    /**
     * testMerchantEqBrand
     *
     * Starting from an empty db - import one fixtured feed item
     * This is an unapproved brand that with same values from brand and merchant
     */
    public function testMerchantEqBrand()
    {
        $this->command->testProcessItem('fixionalprovider-levi-502');

        $this->assertSame(1, $this->repos['item']->findBy(array())->count());
        $this->assertNotNull($this->repos['item']->findOneBy(array('feedId' => 'fixionalprovider-levi-502')));

        $this->assertSame(1, $this->repos['user']->findBy(array())->count());
        $this->assertSame(1, $this->repos['merchant']->findBy(array())->count());
        $this->assertNotNull($this->repos['merchant']->findOneBy(array('name' => 'Levi.com')));
        $this->assertSame(0, $this->repos['brand']->findBy(array())->count());
    }

    /**
     * testMerchantNotOnWhitelist
     *
     * Starting from an empty db - import one fixtured feed item
     * This is an unapproved merchant
     */
    public function testMerchantNotOnWhitelist()
    {
        $this->command->testProcessItem('brand-good-merchant-bad');

        $this->assertSame(1, $this->repos['item']->findBy(array())->count());
        $this->assertNotNull($this->repos['item']->findOneBy(array('feedId' => 'brand-good-merchant-bad')));

        $this->assertSame(1, $this->repos['user']->findBy(array())->count());
        $this->assertSame(0, $this->repos['merchant']->findBy(array())->count());
        $this->assertSame(1, $this->repos['brand']->findBy(array())->count());
        $this->assertNotNull($this->repos['brand']->findOneBy(array('name' => 'Thompson')));
    }

    /**
     * testBrandNotOnWhitelist
     *
     * Starting from an empty db - import one fixtured feed item
     * This is an unapproved brand
     */
    public function testBrandNotOnWhitelist()
    {
        $this->command->testProcessItem('merchant-good-brand-bad');

        $this->assertSame(1, $this->repos['item']->findBy(array())->count());
        $this->assertNotNull($this->repos['item']->findOneBy(array('feedId' => 'merchant-good-brand-bad')));

        $this->assertSame(1, $this->repos['user']->findBy(array())->count());
        $this->assertSame(1, $this->repos['merchant']->findBy(array())->count());
        $this->assertNotNull($this->repos['merchant']->findOneBy(array('name' => 'Levi.com')));
        $this->assertSame(0, $this->repos['brand']->findBy(array())->count());
    }

    /**
     * testBothOnWhitelist
     *
     * Starting from an empty db - import one fixtured feed item
     * Everybody's fine
     */
    public function testBothOnWhitelist()
    {
        $this->command->testProcessItem('brand-and-merchant-good');

        $this->assertSame(1, $this->repos['item']->findBy(array())->count());
        $this->assertNotNull($this->repos['item']->findOneBy(array('feedId' => 'brand-and-merchant-good')));

        $this->assertSame(2, $this->repos['user']->findBy(array())->count());
        $this->assertSame(1, $this->repos['merchant']->findBy(array())->count());
        $this->assertNotNull($this->repos['merchant']->findOneBy(array('name' => 'Levi.com')));
        $this->assertSame(1, $this->repos['brand']->findBy(array())->count());
        $this->assertNotNull($this->repos['brand']->findOneBy(array('name' => 'Thompson')));
    }
}
