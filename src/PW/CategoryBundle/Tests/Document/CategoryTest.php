<?php

namespace PW\CategoryBundle\Tests\Document;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\CategoryBundle\Document\Category;

/**
 * CategoryTest
 */
class CategoryTest extends AbstractTest
{
    /**
     * Test creating a new Category
     */
    public function testDefaults()
    {
        $doc = new Category();
        $this->assertTrue($doc->getIsActive());
    }
}
