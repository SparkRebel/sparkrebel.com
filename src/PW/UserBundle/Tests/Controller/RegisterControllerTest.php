<?php

namespace PW\UserBundle\Tests\Controller;

use PW\ApplicationBundle\Tests\AbstractTest,
    Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * RegisterControllerTest
 */
class RegisterControllerTest extends AbstractTest
{
    /**
     * testTemplates
     */
    public function testTemplates()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/register/templates');
        $response = $client->getResponse();

        $rawcontent = $response->getContent();

        $this->assertSame(200, $response->getStatusCode());

        $count = $crawler->filter('html:contains("Please select at least one of the options.")')->count();
        $this->assertSame(1, $count, "Error message, which shouldn't really be in the template at all, is missing");
    }
}
