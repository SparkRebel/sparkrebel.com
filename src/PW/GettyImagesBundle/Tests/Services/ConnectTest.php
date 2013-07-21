<?php

namespace PW\GettyImagesBundle\Tests\Services;

use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * @group pw_getty_images
 */
class ConnectTest extends WebTestCase
{
    public function testCreatingSession()
    {
        $api = $this->getContainer()->get('pw_getty_images.connect.service');
        dd($api->createSession());
        exit;
    }
}
