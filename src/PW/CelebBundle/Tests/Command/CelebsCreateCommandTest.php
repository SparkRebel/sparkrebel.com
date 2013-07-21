<?php

namespace PW\UserBundle\Tests;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\UserBundle\Document\User;

class CelebsCreateCommandTest extends AbstractTest
{

   	protected $_fixtures = array(
        'PW\UserBundle\DataFixtures\MongoDB\TestUsers', //just use to clear the collection
    );


    /**
     * @test
     * @covers PW\UserBundle\Command\CelebsCreateCommand::execute
     * @covers PW\UserBundle\Command\CelebsCreateCommand::addCelebsUser
     */
    public function testSuccessfulExecution()
    {
        $return = $this->runCommand('celeb:create:user');
        $celebs = $this->_dm->getRepository('PWUserBundle:User')
            ->findOneByName('Celebs');

        $this->assertNotNull($celebs, 'Celebs user was created');
        $this->assertContains('Celebs user has been created successfully', $return);
    }

	/**
     * @test
     * @covers PW\UserBundle\Command\CelebsCreateCommand::execute
     * @covers PW\UserBundle\Command\CelebsCreateCommand::addCelebsUser
     */
    public function testPreventingTwiceInsertingCelebs()
    {
        $this->runCommand('celeb:create:user');

        $return = $this->runCommand('celeb:create:user');

        $this->assertContains('Celebs user already exists', $return);
    }


}
