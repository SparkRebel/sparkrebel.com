<?php

namespace PW\InviteBundle\Tests\Document;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\InviteBundle\Document\Code;

/**
 * CodeTest
 */
class CodeTest extends AbstractTest
{
    /**
     * Test creating a new Code
     */
    public function testDefaults()
    {
        $doc = new Code();
        $this->assertTrue($doc->getIsActive());
    }
}
