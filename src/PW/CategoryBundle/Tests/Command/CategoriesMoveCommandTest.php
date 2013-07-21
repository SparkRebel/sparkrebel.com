<?php

namespace PW\AssetBundle\Tests;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\CategoryBundle\Command\CategoriesMoveCommand,
    PW\CategoryBundle\Document\Category;

class CategoriesMoveCommandTest extends AbstractTest
{
    protected $_fixtures = array(
        'PW\UserBundle\DataFixtures\MongoDB\TestUsers',
        'PW\CategoryBundle\DataFixtures\MongoDB\TestCategories',
        'PW\BoardBundle\DataFixtures\MongoDB\TestBoards',
        'PW\PostBundle\DataFixtures\MongoDB\TestPosts',
    );

    /**
     * @test
     * @covers PW\CategoryBundle\Command\CategoriesMoveCommand::execute
     */
    public function testMovesBoards()
    {
        $this->doTestByType('PWBoardBundle:Board', 'id');
    }

    /**
     * @test
     * @covers PW\CategoryBundle\Command\CategoriesMoveCommand::execute
     */
    public function testMovesBoardsByName()
    {
        $this->doTestByType('PWBoardBundle:Board', 'name');
    }

    /**
     * @test
     * @covers PW\CategoryBundle\Command\CategoriesMoveCommand::execute
     */
    public function testMovesPosts()
    {
        $this->doTestByType('PWPostBundle:Post', 'id');
    }

    /**
     * @test
     * @covers PW\CategoryBundle\Command\CategoriesMoveCommand::execute
     */
    public function testMovesPostsByName()
    {
        $this->doTestByType('PWPostBundle:Post', 'name');
    }

    /**
     * Helper function to run a test for a document type
     *
     * @param string $type
     * @param string $arg
     */
    public function doTestByType($type, $arg = 'id')
    {
        $fromCategory = $this->_dm->getRepository('PWCategoryBundle:Category')
            ->findOneByName('Test Category #1');

        $toCategory = $this->_dm->getRepository('PWCategoryBundle:Category')
            ->findOneByName('Test Category #2');

        $beforeCounts = $this->getCounts($type, $fromCategory, $toCategory);
        $this->assertGreaterThan(0, $beforeCounts['from']);
        $this->assertGreaterThan(0, $beforeCounts['to']);

        if ($arg == 'id') {
            $this->runCommand('category:move', array('from' => $fromCategory->getId(), 'to' => $toCategory->getId()));
        } else {
            $this->runCommand('category:move', array('from' => $fromCategory->getName(), 'to' => $toCategory->getName()));
        }

        $afterCounts = $this->getCounts($type, $fromCategory, $toCategory);
        $this->assertEquals(0, $afterCounts['from']);
        $this->assertEquals($beforeCounts['to'], $afterCounts['to'] - $beforeCounts['to']);
    }

    /**
     * Helper function to return counts for a document type
     *
     * @param string $type
     * @param Category $fromCategory
     * @param Category $toCategory
     * @return array
     */
    public function getCounts($type, Category $fromCategory, Category $toCategory)
    {
        $total = array('from' => 0, 'to' => 0,);

        $total['from'] = $this->_dm->createQueryBuilder($type)
            ->field('category.$id')->equals(new \MongoId($fromCategory->getId()))
            ->count()->getQuery()->execute();

        $total['to'] = $this->_dm->createQueryBuilder($type)
            ->field('category.$id')->equals(new \MongoId($toCategory->getId()))
            ->count()->getQuery()->execute();

        return $total;
    }
}
