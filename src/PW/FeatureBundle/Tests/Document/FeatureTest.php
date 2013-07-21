<?php

namespace PW\FeatureBundle\Tests\Document;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\FeatureBundle\Document\Feature;

/**
 * FeatureTest
 */
class FeatureTest extends AbstractTest
{
    /**
     * Test creating a new Feature
     */
    public function testDefaults()
    {
        $doc = new Feature();
        $this->assertTrue($doc->getIsActive());
    }
}
