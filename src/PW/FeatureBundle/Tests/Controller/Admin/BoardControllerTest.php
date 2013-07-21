<?php

namespace PW\FlagBundle\Tests\Controller\Admin;

use PW\ApplicationBundle\Tests\AbstractTest,
    Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * BoardControllerTest
 */
class BoardControllerTest extends AbstractTest
{
    /**
     * testTemplates
     */
    public function testTemplates()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'GET',
            '/admin/feature/board/templates',
            array(),
            array(),
            array('PHP_AUTH_USER' => 'seequin', 'PHP_AUTH_PW' => 'admin')
        );
        $response = $client->getResponse();

        $rawcontent = $response->getContent();

        $this->assertSame(200, $response->getStatusCode());

        $count = $crawler->filter('html:contains("Edit Featured Board")')->count();
        $this->assertSame(1, $count, "Header missing from response");
    }
}
