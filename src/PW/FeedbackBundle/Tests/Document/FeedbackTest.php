<?php

namespace PW\FeedbackBundle\Tests\Document;

use PW\ApplicationBundle\Tests\AbstractTest,
    PW\FeedbackBundle\Document\Feedback;

/**
 * FeedbackTest
 */
class FeedbackTest extends AbstractTest
{
    /**
     * Test creating a new Feedback
     */
    public function testDefaults()
    {
        $doc = new Feedback();
        $this->assertTrue($doc->getIsActive());
    }
}
