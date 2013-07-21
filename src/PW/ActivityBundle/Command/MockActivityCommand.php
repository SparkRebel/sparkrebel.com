<?php

namespace PW\ActivityBundle\Command;

use PW\ApplicationBundle\Command\AbstractMockCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MockActivityCommand extends AbstractMockCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('mock:activity')
             ->setDescription('Generate random activity for a User')
             ->addOption('type', '-t', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Type to generate');
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \RuntimeException('Not implemented (yet)');
    }
}
