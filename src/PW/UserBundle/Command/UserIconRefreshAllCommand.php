<?php

namespace PW\UserBundle\Command;

use PW\ApplicationBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserIconRefreshAllCommand extends AbstractCommand
{
    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('user:icon:refresh:all')
            ->setDescription('Select all users and refresh their avatars from facebook')
            ->setDefinition(array());
    }

    /**
     * executes icon refresh for all. Sometimes there is a blank asset, sometimes there is no asset, so we need to force it.
     * Since we done have that much number of users, for now it will be sufficent
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $users = $this->getUserManager()
            ->getRepository()
            ->createQueryBuilder()
            ->field('isActive')->equals(true)
            ->getQuery()->execute();

        foreach ($users as $user) {
            $this->getEventManager()->requestJob("user:icon:refresh {$user->getId()}");
        }
    }
}
