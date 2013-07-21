<?php

namespace PW\InviteBundle\Tests\Document;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\InviteBundle\Document\Request;

/**
 * RequestTest
 */
class RequestTest extends AbstractTest
{
    /**
     * Test creating a new Request
     */
    public function testDefaults()
    {
        $doc = new Request();
        $this->assertTrue($doc->getIsActive());
    }
}
