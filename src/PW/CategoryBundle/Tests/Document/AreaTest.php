<?php

namespace PW\CategoryBundle\Tests\Document;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\CategoryBundle\Document\Area;

/**
 * AreaTest
 */
class AreaTest extends AbstractTest
{
    /**
     * Test creating a new Area
     */
    public function testDefaults()
    {
        $doc = new Area();
        $this->assertTrue($doc->getIsActive());
    }
}
