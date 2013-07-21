<?php

namespace PW\TagBundle\Tests\Document;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\TagBundle\Document\Tag;

/**
 * TagTest
 */
class TagTest extends AbstractTest
{
    /**
     * Test creating a new Tag
     */
    public function testDefaults()
    {
        $doc = new Tag();
        $this->assertTrue($doc->getIsActive());
    }
}
