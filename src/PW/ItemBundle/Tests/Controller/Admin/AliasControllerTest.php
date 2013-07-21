<?php

namespace PW\ItemBundle\Tests\Controller\Admin;

use PW\ApplicationBundle\Tests\AbstractTest,
    Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * AliasControllerTest
 */
class AliasControllerTest extends AbstractTest
{
    /**
     * testTemplates
     */
    public function testTemplates()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'GET',
            '/admin/alias/templates',
            array(),
            array(),
            array('PHP_AUTH_USER' => 'seequin', 'PHP_AUTH_PW' => 'admin')
        );
        $response = $client->getResponse();

        $rawcontent = $response->getContent();

        $this->assertSame(200, $response->getStatusCode());

        $count = $crawler->filter('html:contains("Add/Edit alias")')->count();
        $this->assertSame(1, $count, "Title for modal dialog missing");
    }
}
