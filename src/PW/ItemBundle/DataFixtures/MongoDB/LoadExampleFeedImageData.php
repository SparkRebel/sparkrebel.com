<?php

namespace PW\ItemBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadExampleFeedImageData
 */
class LoadExampleFeedImageData extends AbstractFixture
{
    /**
     * Load some example data
     *
     * @param mixed $manager instance
     */
    public function load(ObjectManager $manager)
    {
        $assetsDir = '/var/feeds/assets';

        if (!is_dir($assetsDir)) {
            $assetsDir = sys_get_temp_dir();
        }

        copy('web/images/logo.png', "$assetsDir/hash.png");
    }
}
