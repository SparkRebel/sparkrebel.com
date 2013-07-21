<?php

namespace PW\ApplicationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * Listens for jobs, and performs them
 */
class JobWorkerCommand extends ContainerAwareCommand
{
    protected $env = 'dev';

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('job:worker')
            ->setDescription('Delayed job worker - this is for testing purposes only, use bin/gman to launch worker processes');
    }

    /**
     * execute
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->env = $this->getContainer()->get('kernel')->getEnvironment();

        if ($this->env === 'test') {
            $queue = 'test.console';
        } else {
            $queue = 'console';
        }

        $worker = new \GearmanWorker();
        $worker->addServer();
        $worker->addFunction($queue, array($this, 'cliCall'));

        while ($worker->work()) {
            if ($worker->returnCode() != GEARMAN_SUCCESS) {
                echo "return_code: " . $worker->returnCode() . "\n";
                break;
            }
        }
    }

    /**
     * cliCall
     *
     * @param mixed $job instance
     *
     * @return return value for the executed command
     */
    public function cliCall($job)
    {
        $command = "php app/console --verbose --env={$this->env} " . $job->workload();
        echo "$command\n";

        passthru($command, $return);
        return $return;
    }
}
