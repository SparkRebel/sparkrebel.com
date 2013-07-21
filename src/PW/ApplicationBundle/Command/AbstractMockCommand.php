<?php

namespace PW\ApplicationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractMockCommand extends AbstractCommand
{
    /**
     * Checks whether the command is enabled or not in the current environment
     *
     * Override this to check for x or y and return false if the command can not
     * run properly under the current conditions.
     *
     * @return Boolean
     */
    public function isEnabled()
    {
        return !($this->getContainer()->getParameter('kernel.environment') === 'prod');
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDefinition(array(
            new InputOption('id', null, InputOption::VALUE_REQUIRED, 'User ID to update'),
            new InputOption('email', null, InputOption::VALUE_REQUIRED, 'User Email to update'),
            new InputOption('total', null, InputOption::VALUE_OPTIONAL, 'Total random things to generate', 1)
        ));
    }

    /**
     * Initializes the command just after the input has been validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('total')) {
            throw new \RuntimeException('Total is missing or invalid (must be greater than zero)');
        }
    }
}
