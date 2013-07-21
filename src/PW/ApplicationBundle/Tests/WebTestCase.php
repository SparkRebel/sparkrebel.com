<?php

namespace PW\ApplicationBundle\Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase as BaseWebTestCase;

class WebTestCase extends BaseWebTestCase
{
    /**
     * @param array $classNames List of fully qualified class names of fixtures to load
     * @param string $omName The name of object manager to use
     * @param string $registryName The service id of manager registry to use
     * @param int $purgeMode Sets the ORM purge mode
     *
     * @return null|Doctrine\Common\DataFixtures\Executor\AbstractExecutor
     */
    protected function loadFixtures(array $classNames, $omName = null, $registryName = 'doctrine', $purgeMode = null)
    {
        $container = $this->getContainer();
        $om = $container->get('doctrine_mongodb.odm.document_manager');
        $type = 'MongoDB';

        $executorClass = 'Doctrine\\Common\\DataFixtures\Executor\\'.$type.'Executor';
        $purgerClass = 'Doctrine\\Common\\DataFixtures\Purger\\'.$type.'Purger';
        $purger = new $purgerClass();
        if (null !== $purgeMode) {
            $purger->setPurgeMode($purgeMode);
        }

        $executor = new $executorClass($om, $purger);
        $executor->purge();

        $loader = $this->getFixtureLoader($container, $classNames);
        $executor->execute($loader->getFixtures(), true);

        return $executor;
    }
}
