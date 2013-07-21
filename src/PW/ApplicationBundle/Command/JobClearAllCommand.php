<?php

namespace PW\ApplicationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * Wipes clean the console job queListens for jobs, and performs them
 */
class JobClearAllCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('job:clear:all')
            ->setDescription('Delete the job queue');
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env = $this->getContainer()->get('kernel')->getEnvironment();

        $output->write("gearman -t 1000 -I -w -f console > /dev/null\n");
        `gearman -t 1000 -I -w -f console > /dev/null`;
    }
}
