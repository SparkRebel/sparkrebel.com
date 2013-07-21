<?php

namespace PW\BoardBundle\Tests\Model;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\UserBundle\Document\User,
    PW\PostBundle\Document\Post;

class BoardManagerTest extends AbstractTest
{
    /**
     * @covers PW\ApplicationBundle\Model\AbstractManager::save
     * @expectedException PW\ApplicationBundle\Document\Exception\ConstraintViolationException
     */
    public function testInvalidBoardSaveThrowsExceptionsByDefault()
    {
        /* @var $boardManager \PW\BoardBundle\Model\BoardManager */
        $boardManager = $this->container->get('pw_board.board_manager');

        /* @var $board \PW\BoardBundle\Document\Board */
        $board = $boardManager->create();

        $boardManager->save($board);
    }

    /**
     * @covers PW\ApplicationBundle\Model\AbstractManager::save
     */
    public function testInvalidBoardSaveReturnsConstraintViolationList()
    {
        /* @var $boardManager \PW\BoardBundle\Model\BoardManager */
        $boardManager = $this->container->get('pw_board.board_manager');

        /* @var $board \PW\BoardBundle\Document\Board */
        $board = $boardManager->create();

        $errors = $boardManager->save($board, array('exceptions' => false));

        $this->assertInstanceOf('Symfony\Component\Validator\ConstraintViolationList', $errors);
    }
	
    /**
     * @covers PW\BoardBundle\Model\BoardManager::changeBoard
     */
    public function testChangeBoard()
    {
        /* @var $boardManager \PW\BoardBundle\Model\BoardManager */
        $boardManager = $this->container->get('pw_board.board_manager');

        $GLOBALS['FIXTURE_USERS_TOTAL']  = 1;
        $GLOBALS['FIXTURE_BOARDS_TOTAL'] = 2;
        $GLOBALS['FIXTURE_POSTS_TOTAL']  = 0;

        $this->_loadFixtures(array(
            'PW\UserBundle\DataFixtures\MongoDB\TestUsers',
            'PW\CategoryBundle\DataFixtures\MongoDB\TestCategories',
            'PW\BoardBundle\DataFixtures\MongoDB\TestBoards',
            'PW\PostBundle\DataFixtures\MongoDB\TestPosts',
        ), false);
		
    		$board1 = $this->_dm->getRepository('PWBoardBundle:Board')->findOneBySlug('user-1-board-1');
    		$board2 = $this->_dm->getRepository('PWBoardBundle:Board')->findOneBySlug('user-1-board-2');
		
        $post = new Post();
        $post->setCreatedBy(new User());
        $post->setDescription('1.0.0');
		    $post->setBoard($board1);
        $this->_dm->persist($post);
        $this->_dm->flush();
        
        $boardManager->changeBoard($post, $board2);
        
        $this->assertSame($board2->getId(), $post->getBoard()->getId());
        $this->assertSame($board2->getCategory()->getId(), $post->getCategory()->getId());
    }
	
}