<?php

namespace PW\ItemBundle\Tests\Document;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\ItemBundle\Document\Alias;

/**
 * AliasTest
 */
class AliasTest extends AbstractTest
{
    /**
     * Test creating a new Alias
     */
    public function testDefaults()
    {
        $doc = new Alias();
        $this->assertTrue($doc->getIsActive());
    }
}
