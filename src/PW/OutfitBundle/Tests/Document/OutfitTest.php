<?php

namespace PW\OutfitBundle\Tests\Document;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\OutfitBundle\Document\Outfit;

/**
 * OutfitTest
 */
class OutfitTest extends AbstractTest
{
    /**
     * Test creating a new Outfit
     */
    public function testDefaults()
    {
        $doc = new Outfit();
        $this->assertTrue($doc->getIsActive());
    }
}
