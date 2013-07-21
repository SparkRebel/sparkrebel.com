<?php

namespace PW\UserBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager,
    PW\UserBundle\Document\User;

/**
 * LoadExampleData
 */
class LoadExampleData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * getOrder
     *
     * No Pre-load dependencies, load almost first
     *
     * @return int
     */
    public function getOrder()
    {
        return 20;
    }

    /**
     * load some example data
     *
     * @param mixed $manager instance
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 10; $i++) {
            $document = new User();
            $document->setEmail("testuser{$i}@example.com");
            $document->setName("testuser{$i}");
            $document->setPlainPassword('test');
            $document->setEnabled(true);
            $document->addRole('ROLE_USER');
            $this->addReference("User-{$i}", $document);
            $manager->persist($document);
        }

        for ($i = 1; $i <= 2; $i++) {
            $document = new User();
            $document->setEmail("testadmin{$i}@example.com");
            $document->setName("testadmin{$i}");
            $document->setPlainPassword('test');
            $document->setEnabled(true);
            $document->addRole('ROLE_ADMIN');
            $this->addReference("AdminUser-{$i}", $document);
            $manager->persist($document);
        }

        $manager->flush();
    }
}
