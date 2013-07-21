<?php

namespace PW\UserBundle\Tests\Document;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\UserBundle\Document\User;

/**
 * UserTest
 */
class UserTest extends AbstractTest
{
    /**
     * Test creating a new User
     */
    public function testDefaults()
    {
        $doc = new User();
        $this->assertTrue($doc->getIsActive());
    }
}
