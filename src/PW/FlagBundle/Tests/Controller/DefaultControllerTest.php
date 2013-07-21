<?php

namespace PW\FlagBundle\Tests\Controller;

use PW\ApplicationBundle\Tests\AbstractTest,
    Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * FlagControllerTest
 */
class FlagControllerTest extends AbstractTest
{
    /**
     * testTemplates
     */
    public function testTemplates()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/flag/templates');
        $response = $client->getResponse();

        $rawcontent = $response->getContent();

        $this->assertSame(200, $response->getStatusCode());

        $count = $crawler->filter('html:contains("What\'s wrong with this picture?")')->count();
        $this->assertSame(1, $count, "Title for modal dialog missing");
    }
}
