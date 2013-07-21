<?php

namespace PW\FlagBundle\Tests\Document;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\FlagBundle\Document\Flag;

/**
 * FlagTest
 */
class FlagTest extends AbstractTest
{
    /**
     * Test creating a new Flag
     */
    public function testDefaults()
    {
        $doc = new Flag();
        $this->assertTrue($doc->getIsActive());
    }
}
