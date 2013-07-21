<?php

namespace PW\ItemBundle\Tests\Document;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\ItemBundle\Document\Whitelist;

/**
 * WhitelistTest
 */
class WhitelistTest extends AbstractTest
{
    /**
     * Test creating a new Whitelist
     */
    public function testDefaults()
    {
        $doc = new Whitelist();
        $this->assertTrue($doc->getIsActive());
    }
}
