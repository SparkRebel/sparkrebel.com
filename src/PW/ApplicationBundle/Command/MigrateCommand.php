<?php

namespace PW\ApplicationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * When a new user follows another user - Create some backdated feed data for them
 */
class MigrateCommand extends ContainerAwareCommand
{

    /**
     * document manager placeholder instance
     */
    protected $dm;

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('data:migrate')
            ->setDescription('migrate application data')
            ->setDefinition(array(
                new InputArgument(
                    'to',
                    InputArgument::REQUIRED,
                    'the version to migrate to'
                ),
            ));
    }

    /**
     * execute
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
    }
}