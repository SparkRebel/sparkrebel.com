<?php

namespace PW\BoardBundle\Tests\Document;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\BoardBundle\Document\Board,
    PW\AssetBundle\Document\Asset;

/**
 * @group board
 * @group frontend
 */
class BoardTest extends AbstractTest
{
    /**
     * Test creating a new Board
     */
    public function testDefaults()
    {
        $doc = new Board();
        $this->assertTrue($doc->getIsActive());
    }

    /**
     * Test that the image limit on Boards is enforced
     *
     * @covers \PW\BoardBundle\Document\Board::addImages
     */
    public function testImageLimitEnforced()
    {
        $GLOBALS['FIXTURE_USERS_TOTAL']  = 1;
        $GLOBALS['FIXTURE_BOARDS_TOTAL'] = 1;

        $this->_loadFixtures(array(
            'PW\UserBundle\DataFixtures\MongoDB\TestUsers',
            'PW\CategoryBundle\DataFixtures\MongoDB\TestCategories',
            'PW\BoardBundle\DataFixtures\MongoDB\TestBoards',
        ), false);

        $asset1 = new Asset();
        $asset1->setUrl(mt_rand());
        $this->_dm->persist($asset1);

        $asset2 = new Asset();
        $asset2->setUrl(mt_rand());
        $this->_dm->persist($asset2);

        $asset3 = new Asset();
        $asset3->setUrl(mt_rand());
        $this->_dm->persist($asset3);

        $asset4 = new Asset();
        $asset4->setUrl(mt_rand());
        $this->_dm->persist($asset4);

        $asset5 = new Asset();
        $asset5->setUrl(mt_rand());
        $this->_dm->persist($asset5);
        
        $this->_dm->flush();

        /* @var $board \PW\BoardBundle\Document\Board */
        $board = $this->_dm->getRepository('PWBoardBundle:Board')->findOneByName('User #1 - Board #1');
        $board->addImages($asset1);
        $board->addImages($asset2);
        $board->addImages($asset3);
        $board->addImages($asset4);
        $board->addImages($asset5);
        $this->_dm->persist($board);
        $this->_dm->flush($board);

        $board = $this->_dm->getRepository('PWBoardBundle:Board')->findOneByName('User #1 - Board #1');
        $this->assertEquals(4, $board->getImages()->count());

        return $board;
    }

    /**
     * Test that a Board owner's BoardCount gets incremented
     *
     * @depends testImageLimitEnforced
     * @covers \PW\BoardBundle\Document\Board::prePersist
     */
    public function testUserBoardCountIncremented(Board $board)
    {
        $this->assertEquals(1, $board->getCreatedBy()->getCounts()->getBoards());
    }
}
