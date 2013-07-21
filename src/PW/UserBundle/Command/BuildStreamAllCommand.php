<?php

namespace PW\UserBundle\Command;

use PW\ApplicationBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildStreamAllCommand extends AbstractCommand
{
    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('build:stream:all')
            ->setDescription('Builds new unified stream for all users')
            ->setDefinition(array());
    }

    /**
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
            $output->writeln("<info>Processing User {$user->getName()}</info>");
            $this->getEventManager()->requestJob("build:stream {$user->getId()}");
        }
    }
}
