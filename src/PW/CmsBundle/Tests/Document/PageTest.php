<?php

namespace PW\CmsBundle\Tests\Document;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\CmsBundle\Document\Page;

/**
 * PageTest
 */
class PageTest extends AbstractTest
{
    /**
     * Test creating a new Page
     */
    public function testDefaults()
    {
        $doc = new Page();
        $this->assertNotEmpty($doc->getIsActive());
    }
}
