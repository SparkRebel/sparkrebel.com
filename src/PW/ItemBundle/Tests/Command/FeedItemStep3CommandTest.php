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
class FeedItemStep1Command3 extends \PW\ItemBundle\Command\FeedItemStep1Command
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
 * FeedItemStep3Command
 *
 * Overriden methods for testing purposes only
 */
class FeedItemStep3Command extends \PW\ItemBundle\Command\FeedItemStep3Command
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
     * If the item being tested doesn't have an image (most won't) add a random image
     * This prevents validation errors
     *
     * @param mixed $id feed item instance or id
     *
     * @return processed item instance
     */
    public function testProcessItem($id)
    {
        $item = $this->repos['Item']->findOneBy(array('feedId' => $id));
        if ($item && !$item->getImagePrimary()) {
            $assetRepo = $this->dm->getRepository('PWAssetBundle:Asset');
            $item->setImagePrimary($assetRepo->findOneBy(array()));
        }


        return $this->processItem($id);
    }
}

/**
 * Step3Test
 */
class Step3Test extends AbstractTest
{
    /**
     * repos used by this test
     */
    protected $repos = array();

    /**
     * fixtures to load before each test
     */
    protected $_fixtures = array(
        'PW\AssetBundle\DataFixtures\MongoDB\LoadExampleData',
        'PW\CategoryBundle\DataFixtures\MongoDB\LoadExampleData',
        'PW\UserBundle\DataFixtures\MongoDB\LoadExampleData',
        'PostStep1Data',
        'LoadExampleFeedItemData',
        'LoadExampleWhitelistData'
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

        $this->step1 = new FeedItemStep1Command3($this->container);
        $this->command = new FeedItemStep3Command($this->container);

        $this->repos['board'] = $this->_dm->getRepository('PWBoardBundle:Board');
        $this->repos['brand'] = $this->_dm->getRepository('PWUserBundle:Brand');
        $this->repos['category'] = $this->_dm->getRepository('PWCategoryBundle:Category');
        $this->repos['item'] = $this->_dm->getRepository('PWItemBundle:Item');
        $this->repos['feeditem'] = $this->step1->feedsDm->getRepository('PWItemBundle:FeedItem');
        $this->repos['post'] = $this->_dm->getRepository('PWPostBundle:Post');
        $this->repos['user'] = $this->_dm->getRepository('PWUserBundle:User');
        $this->repos['merchant'] = $this->_dm->getRepository('PWUserBundle:Merchant');

        $this->postManager = $this->container->get('pw_post.post_manager');
    }
    
    /**
     * testSimple
     *
     * Starting from an empty db - import one fixtured feed item
     * This is an unapproved brand, should therefore create only 1 post
     *
     * @param boolean $runCommand Whether to run the command or just the tests
     */
    public function testSimple($runCommand = true)
    {
        $fid = 'fixionalprovider-levi-501';
        if ($runCommand) {
            $this->command->testProcessItem($fid);
        }
        
        $item = $this->repos['item']->findOneBy(array('feedId' => $fid));
        $this->assertSame(true, $item->getIsActive());
        
        $brand = $this->repos['brand']->findOneBy(array('name' => 'Levi.com'));
        $this->assertNull($brand);
        
        $merchant = $this->repos['merchant']->findOneBy(array('name' => 'Levi.com'));
        $this->assertNotNull($merchant);
        
        $board = $this->repos['board']->findOneBy(array(
                'name' => "Bottoms"
        ));
        $this->assertNotNull($board);
        $this->assertSame('Levi.com', $board->getCreatedBy()->getName());
        $this->assertSame('Bottoms', $board->getCategory()->getName());
        
        $post = $this->repos['post']->findOneBy(array(
                'description' => "The definitive shrink to fit jeans"
        ));
        $this->assertNotNull($post);
        
        $boards = $this->repos['board']->createQueryBuilder()
            ->count()
            ->field('createdBy')->references($merchant)
            ->getQuery()->execute();
        $this->assertSame(1, $boards);
        
        $posts = $this->repos['post']->createQueryBuilder()
            ->count()
            ->field('target')->references($item)
            ->getQuery()->execute();
        $this->assertSame(1, $posts);
        
        $this->assertSame($fid, $post->getTarget()->getFeedId());
        $this->assertSame($board->getId(), $post->getBoard()->getId());
        $this->assertSame('Levi.com', $post->getCreatedBy()->getName());
        $this->assertSame('Bottoms', $post->getCategory()->getName());
        $this->assertSame('merchant', $post->getUserType());
    }
        
    /**
     * testIdempotence
     *
     * It should be possible to rerun with absolutely no concequences
     * This is an unapproved brand
     */
    public function testIdempotence()
    {
        $fid = 'fixionalprovider-levi-501';
        $this->command->testProcessItem($fid);
        $this->testSimple();
    }
        
    /**
     * testMerchantEqBrandIsMerchant
     *
     * Starting from an empty db - import one fixtured feed item
     * This is an approved brand that with same values from brand and merchant
     * whitelisted as a merchant
     */
    public function testMerchantEqBrandIsMerchant()
    {
        $fid = 'fixionalprovider-levi-502';
        $this->step1->testProcessItem($fid);
        $this->command->testProcessItem($fid);
        $item = $this->repos['item']->findOneBy(array('feedId' => $fid));
        
        $brand = $this->repos['brand']->findOneBy(array());
        $this->assertNull($brand);
        $merchant = $this->repos['merchant']->findOneBy(array());
        $this->assertSame('Levi.com', $merchant->getName());
        
        $board = $this->repos['board']->findOneBy(array(
                'createdBy.$id' => new \MongoId($merchant->getId()),
                'name' => 'Bottoms'
        ));
        $this->assertNotNull($board);
        $this->assertSame('Bottoms', $board->getCategory()->getName());
        $post = $this->repos['post']->createQueryBuilder()
            ->field('board')->references($board)
            ->field('createdBy')->references($merchant)
            ->field('target')->references($item)
            ->getQuery()->execute()->getSingleResult();
        $this->assertNotNull($post);
        $this->assertSame('Bottoms', $post->getCategory()->getName());
        $this->assertSame($fid, $post->getTarget()->getFeedId());
        $this->assertSame('merchant', $post->getUserType());
        
        //test rootPost for Item
        $this->assertNotNull($item->getRootPost());
        $this->assertSame($item->getRootPost()->getId(), $post->getId());
    }
        
    /**
     * testMerchantEqBrandIsBrand
     *
     * Starting from an empty db - import one fixtured feed item
     * This is an approved brand that with same values from brand and merchant
     * whitelisted as a brand
     */
    public function testMerchantEqBrandIsBrand()
    {
        $fid = 'fixionalprovider-thompson-503';
        $this->step1->testProcessItem($fid);
        $this->command->testProcessItem($fid);
        
        $item = $this->repos['item']->findOneBy(array('feedId' => $fid));
        
        $brand = $this->repos['brand']->findOneBy(array('name' => 'Thompson'));
        $this->assertNotNull($brand);
        
        $merchant = $this->repos['merchant']->findOneBy(array('name' => 'Thompson'));
        $this->assertNull($merchant);
        
        $boards = $this->repos['board']->createQueryBuilder()
            ->field('createdBy')->references($brand)
            ->getQuery()->execute();
        $this->assertSame(1, count($boards));
        
        $posts = $this->repos['post']->createQueryBuilder()
            ->field('target')->references($item)
            ->getQuery()->execute();
        $this->assertSame(1, count($posts));
        
        $board = $this->repos['board']->findOneBy(array(
                'createdBy.$id' => new \MongoId($brand->getId()),
                'name' => 'Bottoms'
        ));
        $this->assertNotNull($board);
        $this->assertSame('Bottoms', $board->getCategory()->getName());
        $post = $this->repos['post']->findOneBy(array(
                'board.$id' => new \MongoId($board->getId()),
                'createdBy.$id' => new \MongoId($brand->getId())
        ));
        $this->assertNotNull($post);
        $this->assertSame('Bottoms', $post->getCategory()->getName());
        $this->assertSame($fid, $post->getTarget()->getFeedId());
        $this->assertSame('brand', $post->getUserType());
    }
        
    /**
     * testMerchantNotOnWhitelist
     *
     * Starting from an empty db - import one fixtured feed item
     * This is an unapproved merchant
     */
    public function testMerchantNotOnWhitelist()
    {
        $fid = 'brand-good-merchant-bad';
        $this->step1->testProcessItem($fid);
        $this->command->testProcessItem($fid);
        
        $item = $this->repos['item']->findOneBy(array('feedId' => $fid));
        $this->assertSame(true, $item->getIsActive());
        
        $brand = $this->repos['brand']->findOneBy(array('name' => 'Thompson'));
        $this->assertNotNull($brand);
        
        $merchant = $this->repos['merchant']->findOneBy(array('name' => 'Thompson'));
        $this->assertNull($merchant);
        
        $board = $this->repos['board']->createQueryBuilder()
            ->field('createdBy')->references($brand)
            ->field('name')->equals("Bottoms")
            ->getQuery()->execute()->getSingleResult();
        $this->assertNotNull($board);
        $this->assertSame('Bottoms', $board->getCategory()->getName());
        $post = $this->repos['post']->findOneBy(array(
                'board.$id' => new \MongoId($board->getId()),
                'createdBy.$id' => new \MongoId($brand->getId())
        ));
        $this->assertNotNull($post);
        $this->assertSame('Bottoms', $post->getCategory()->getName());
        $this->assertSame('brand-good-merchant-bad', $post->getTarget()->getFeedId());
        $this->assertSame('brand', $post->getUserType());
        
        $board = $this->repos['board']->createQueryBuilder()
            ->field('createdBy')->references($brand)
            ->field('name')->equals('Thompson @ notonwhitelist')
            ->getQuery()->execute()->getSingleResult();
        $this->assertNotNull($board);
        $this->assertNull($board->getCategory());
        $post = $this->repos['post']->findOneBy(array(
                'board.$id' => new \MongoId($board->getId()),
                'createdBy.$id' => new \MongoId($brand->getId())
        ));
        $this->assertNotNull($post);
        $this->assertNull($post->getCategory());
        $this->assertSame('brand-good-merchant-bad', $post->getTarget()->getFeedId());
        $this->assertSame('brand', $post->getUserType());
        
        $boards = $this->repos['board']->createQueryBuilder()
            ->field('createdBy')->references($brand)
            ->getQuery()->execute();
        $this->assertSame(2, count($boards), "expected exactly 2 baords for this brand");
        
        $posts = $this->repos['post']->createQueryBuilder()
            ->field('target')->references($item)
            ->getQuery()->execute();
        $this->assertSame(2, count($posts), "expected exactly 2 posts for this item");
        
        //test rootPost for Item
        $this->assertNotNull($item->getRootPost());
        $posts = $this->repos['post']->createQueryBuilder()
            ->field('target')->references($item)
            ->sort('created', 'asc')
            ->getQuery()->execute();
        
        $this->assertSame($item->getRootPost()->getId(), $posts->getNext()->getId());
    }
        
    /**
     * testBrandNotOnWhitelist
     *
     * Starting from an empty db - import one fixtured feed item
     * This is an unapproved brand
     */
    public function testBrandNotOnWhitelist()
    {
        $fid = 'merchant-good-brand-bad';
        $this->step1->testProcessItem($fid);
        $this->command->testProcessItem($fid);
        
        $item = $this->repos['item']->findOneBy(array('feedId' => $fid));
        
        $brand = $this->repos['brand']->findOneBy(array('name' => 'notonwhitelist'));
        $this->assertNull($brand);
        
        $merchant = $this->repos['merchant']->findOneBy(array('name' => 'Levi.com'));
        $this->assertNotNull($merchant);
        
        $boards = $this->repos['board']->createQueryBuilder()
            ->field('createdBy')->references($merchant)
            ->getQuery()->execute();
        $this->assertSame(1, count($boards));
        
        $posts = $this->repos['post']->createQueryBuilder()
            ->field('target')->references($item)
            ->getQuery()->execute();
        $this->assertSame(1, count($posts));
        
        $board = $this->repos['board']->findOneBy(array(
                'createdBy.$id' => new \MongoId($merchant->getId()),
                'name' => 'Bottoms'
        ));
        $this->assertNotNull($board);
        $this->assertSame('Bottoms', $board->getCategory()->getName());
        
        $post = $this->repos['post']->createQueryBuilder()
            ->field('board')->references($board)
            ->field('createdBy')->references($merchant)
            ->field('target')->references($item)
            ->getQuery()->execute()->getSingleResult();
        $this->assertNotNull($post);
        $this->assertSame('Bottoms', $post->getCategory()->getName());
        $this->assertSame('merchant', $post->getUserType());
    }
        
    /**
     * testBothOnWhitelist
     *
     * Starting from an empty db - import one fixtured feed item
     * Everybody's fine
     */
    public function testBothOnWhitelist()
    {
        $fid = 'brand-and-merchant-good';
        $this->step1->testProcessItem($fid);
        $this->command->testProcessItem($fid);
        
        $item = $this->repos['item']->findOneBy(array('feedId' => $fid));
        
        $brand = $this->repos['brand']->findOneBy(array('name' => 'Thompson'));
        $this->assertNotNull($brand);
        
        $merchant = $this->repos['merchant']->findOneBy(array('name' => 'Levi.com'));
        $this->assertNotNull($merchant);
        
        $boards = $this->repos['board']->createQueryBuilder()
            ->field('createdBy')->references($brand)
            ->getQuery()->execute();
        $this->assertSame(2, count($boards));
        $boards = $this->repos['board']->createQueryBuilder()
            ->field('createdBy')->references($merchant)
            ->getQuery()->execute();
        $this->assertSame(2, count($boards));
        
        $board = $this->repos['board']->findOneBy(array(
                'createdBy.$id' => new \MongoId($brand->getId()),
                'name' => 'Bottoms'
        ));
        $this->assertNotNull($board);
        $this->assertSame('Bottoms', $board->getCategory()->getName());
        $post = $this->repos['post']->createQueryBuilder()
            ->field('board')->references($board)
            ->field('target')->references($item)
            ->getQuery()->getSingleResult();
        $this->assertNotNull($post);
        $this->assertSame('Bottoms', $post->getCategory()->getName());
        $this->assertSame($fid, $post->getTarget()->getFeedId());
        $this->assertSame('brand', $post->getUserType());
        
        $board = $this->repos['board']->findOneBy(array(
                'createdBy.$id' => new \MongoId($brand->getId()),
                'name' => 'Thompson @ Levi.com'
        ));
        $this->assertNotNull($board);
        $this->assertNull($board->getCategory());
        $post = $this->repos['post']->createQueryBuilder()
            ->field('board')->references($board)
            ->field('target')->references($item)
            ->getQuery()->getSingleResult();
        $this->assertNotNull($post);
        $this->assertNull($post->getCategory());
        $this->assertSame($fid, $post->getTarget()->getFeedId());
        $this->assertSame('brand', $post->getUserType());
        
        $board = $this->repos['board']->findOneBy(array(
                'createdBy.$id' => new \MongoId($merchant->getId()),
                'name' => 'Bottoms'
        ));
        $this->assertNotNull($board);
        $this->assertSame('Bottoms', $board->getCategory()->getName());
        $post = $this->repos['post']->createQueryBuilder()
            ->field('board')->references($board)
            ->field('target')->references($item)
            ->getQuery()->getSingleResult();
        $this->assertNotNull($post);
        $this->assertSame('Bottoms', $post->getCategory()->getName());
        $this->assertSame($fid, $post->getTarget()->getFeedId());
        $this->assertSame('merchant', $post->getUserType());
        
        $board = $this->repos['board']->findOneBy(array(
                'createdBy.$id' => new \MongoId($merchant->getId()),
                'name' => 'Thompson @ Levi.com'
        ));
        $this->assertNotNull($board);
        $this->assertNull($board->getCategory());
        $post = $this->repos['post']->createQueryBuilder()
            ->field('board')->references($board)
            ->field('target')->references($item)
            ->getQuery()->getSingleResult();
        $this->assertNotNull($post);
        $this->assertNull($post->getCategory());
        $this->assertSame($fid, $post->getTarget()->getFeedId());
        $this->assertSame('merchant', $post->getUserType());
    }
        
    /**
     * testBrandNotSupplied
     *
     * Starting from an empty db - import one fixtured feed item
     * This is an empty brand with a valid merchant (who is a brand user)
     */
    public function testBrandNotSupplied()
    {
        $fid = 'no-brand-set';
        $this->step1->testProcessItem($fid);
        $this->command->testProcessItem($fid);
        
        $item = $this->repos['item']->findOneBy(array('feedId' => $fid));
        
        $brand = $this->repos['brand']->findOneBy(array('name' => 'Thompson'));
        $this->assertNotNull($brand);
        
        $merchant = $this->repos['merchant']->findOneBy(array('name' => 'Thompson'));
        $this->assertNull($merchant);
        
        $boards = $this->repos['board']->createQueryBuilder()
            ->field('createdBy')->references($brand)
            ->getQuery()->execute();
        $this->assertSame(1, count($boards));
        
        $posts = $this->repos['post']->createQueryBuilder()
            ->field('target')->references($item)
            ->getQuery()->execute();
        $this->assertSame(1, count($posts));
        
        $board = $this->repos['board']->createQueryBuilder()
            ->field('createdBy')->references($brand)
            ->field('name')->equals('Bottoms')
            ->getQuery()->execute()->getSingleResult();
        $this->assertNotNull($board);
        $this->assertSame('Bottoms', $board->getCategory()->getName());
        $post = $this->repos['post']->createQueryBuilder()
            ->field('board')->references($board)
            ->field('createdBy')->references($brand)
            ->field('target')->references($item)
            ->getQuery()->execute()->getSingleResult();
        $this->assertNotNull($post);
        $this->assertSame('Bottoms', $post->getCategory()->getName());
        $this->assertSame('brand', $post->getUserType());
    }
        
    /**
     * testSaleItem
     *
     * TODO needs a step1 test to go with it (step1 is run because it is responsible for setting the
     * onsale flag on the item)
     */
    public function testSaleItem()
    {
        $fid = 'fixionalprovider-levi-501';
        $this->_loadFixtures('FeedItemReducedPrice');
        $this->step1->testProcessItem($fid);
        $this->command->testProcessItem($fid);
        
        $item = $this->repos['item']->findOneBy(array('feedId' => $fid));
        
        $merchant = $this->repos['merchant']->findOneBy(array('name' => 'Levi.com'));
        $this->assertNotNull($merchant);
        
        $boards = $this->repos['board']->createQueryBuilder()
            ->field('createdBy')->references($merchant)
            ->getQuery()->execute();
        $this->assertSame(2, count($boards));
        
        $posts = $this->repos['post']->createQueryBuilder()
            ->field('target')->references($item)
            ->getQuery()->execute();
        $this->assertSame(2, count($posts));
        
        $board = $this->repos['board']->createQueryBuilder()
            ->field('createdBy')->references($merchant)
            ->field('name')->equals('Bottoms')
            ->getQuery()->execute()->getSingleResult();
        $this->assertNotNull($board);
        $post = $this->repos['post']->createQueryBuilder()
            ->field('board')->references($board)
            ->field('createdBy')->references($merchant)
            ->field('target')->references($item)
            ->getQuery()->execute()->getSingleResult();
        $this->assertNotNull($post);
        $this->assertSame('Bottoms', $post->getCategory()->getName());
        $this->assertSame('merchant', $post->getUserType());
        
        $board = $this->repos['board']->createQueryBuilder()
            ->field('createdBy')->references($merchant)
            ->field('name')->equals('Sale')
            ->getQuery()->execute()->getSingleResult();
        $this->assertNotNull($board);
        $post = $this->repos['post']->createQueryBuilder()
            ->field('board')->references($board)
            ->field('createdBy')->references($merchant)
            ->field('target')->references($item)
            ->getQuery()->execute()->getSingleResult();
        $this->assertNotNull($post);
        $this->assertNull($post->getCategory());
        $this->assertSame('merchant', $post->getUserType());
    }

    public function testCategoryUpdate()
    {
        $fid = 'brand-and-merchant-good';
        $this->step1->testProcessItem($fid);
        $this->command->testProcessItem($fid);

        $item = $this->repos['item']->findOneBy(array('feedId' => $fid));
        $posts = $this->repos['post']->createQueryBuilder()
            ->field('target')->references($item)
            ->getQuery()->execute();

        $categories = $item->getCategories();
        		
        foreach ($posts as $post) {
        	$originalBoards[] = new \MongoId($post->getBoard()->getId());
        }
				
        //$this->assertSame((string)$post->getBoard()->getCategory(), (string)$categories[0]);

        // update
        $manager = $this->step1->feedsDm;
        $zitem = $this->repos['feeditem']->findOneBy(array('fid' => $fid));
        $zitem->setName('cesar');
        $zitem->setStatus('pending');
        $zitem->setCategories(array('sweaters'));
        $manager->persist($zitem);
        $manager->flush();

        // // re-import it
        $this->step1->testProcessItem($fid);
        $this->command->testProcessItem($fid);
        
        //check that only 2 of the original boards are still referenced
        $posts = $this->repos['post']->createQueryBuilder()
            ->field('target')->references($item)
                    ->field('board.$id')->in($originalBoards)
            ->getQuery()->execute();
        $this->assertSame(2, $posts->count());
        
        //check that only 2 other boards have the right category
        $posts = $this->repos['post']->createQueryBuilder()
            ->field('target')->references($item)
                    ->field('board.$id')->notIn($originalBoards)
            ->getQuery()->execute();
        $this->assertSame(2, $posts->count());
        
        foreach ($posts as $post) {
            $this->assertSame((string)$post->getBoard()->getCategory(), 'tops');
        }

    }
}
