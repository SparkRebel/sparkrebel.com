<?php

namespace PW\ItemBundle\Tests\Document;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\ItemBundle\Document\Item;

/**
 * ItemTest
 */
class ItemTest extends AbstractTest
{
    /**
     * Test creating a new Item
     *
     * The default value for isActive for items is false
     */
    public function testDefaults()
    {
        $doc = new Item();
        $this->assertFalse($doc->getIsActive());
    }
}
