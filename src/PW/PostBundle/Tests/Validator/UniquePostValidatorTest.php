<?php

namespace PW\PostBundle\Tests\Validator;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\PostBundle\Document\Post,
    PW\UserBundle\Document\User;

/**
 * UniquePostValidatorTest
 */
class UniquePostValidatorTest extends AbstractTest
{
    /**
     * @var \PW\PostBundle\Repository\PostRepository
     */
    protected $_repository;

    /**
     * fixtures to load before each test
     */
    protected $_fixtures = array(
        'PW\AssetBundle\DataFixtures\MongoDB\LoadExampleData',
        'PW\BoardBundle\DataFixtures\MongoDB\LoadExampleData',
        'PW\CategoryBundle\DataFixtures\MongoDB\LoadExampleData',
        'PW\ItemBundle\DataFixtures\MongoDB\LoadExampleItems',
        'PW\OutfitBundle\DataFixtures\MongoDB\LoadExampleData',
        'PW\UserBundle\DataFixtures\MongoDB\LoadExampleData'
    );

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->repos['post'] = $this->_dm->getRepository('PWPostBundle:Post');
        $this->repos['board'] = $this->_dm->getRepository('PWBoardBundle:Board');
        $this->repos['user'] = $this->_dm->getRepository('PWUserBundle:User');
        $this->repos['asset'] = $this->_dm->getRepository('PWAssetBundle:Asset');
    }

    /**
     * testValidPasses
     */
    public function testValidPasses()
    {
        $validator = $this->container->get('validator');

        $user  = $this->repos['user']->findOneBy(array());
        $board = $this->repos['board']->findOneBy(array());
        $asset = $this->repos['asset']->findOneBy(array());
        $description = "my awesome description";

        $p1 = new Post();
        $p1->setCreatedBy($user);
        $p1->setBoard($board);
        $p1->setImage($asset);
        $p1->setTarget($asset);
        $p1->setCategory($board->getCategory());
        $p1->setDescription($description);

        $violationList = $validator->validate($p1);
        $this->assertEquals(0, $violationList->count());

        $this->_dm->persist($p1);
        $this->_dm->flush();
    }

    /**
     * Tests that duplicate prevention works
     */
    public function testSimple()
    {
        $validator = $this->container->get('validator');

        $user  = $this->repos['user']->findOneBy(array());
        $board = $this->repos['board']->findOneBy(array());
        $asset = $this->repos['asset']->findOneBy(array());
        $description = "my awesome description";

        $p1 = new Post();
        $p1->setCreatedBy($user);
        $p1->setBoard($board);
        $p1->setImage($asset);
        $p1->setTarget($asset);
        $p1->setCategory($board->getCategory());
        $p1->setDescription($description);

        $violationList = $validator->validate($p1);
        $this->assertEquals(0, $violationList->count());

        $this->_dm->persist($p1);
        $this->_dm->flush();

        $p2 = new Post();
        $p2->setCreatedBy($user);
        $p2->setBoard($board);
        $p2->setImage($asset);
        $p2->setTarget($asset);
        $p2->setCategory($board->getCategory());
        $p2->setDescription($description);

        $violationList = $validator->validate($p2);
        $this->assertGreaterThan(0, $violationList->count());

        $this->_dm->persist($p2);
        $this->_dm->flush();
    }
}
