<?php

namespace PW\ApplicationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\StringInput,
    Symfony\Component\Console\Output\OutputInterface;

class BeanstalkdManagerCommand extends ContainerAwareCommand
{
    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('job:worker:beanstalkd')
            ->setDefinition(array(
                new InputArgument('tube', InputArgument::REQUIRED, 'What tube should worker listen to?'),
                new InputArgument('connection', InputArgument::REQUIRED, 'What connection should worker listen to?')
            ))
            ->setDescription('Manages the beanstalk stuff');
    }
    

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tube = $input->getArgument('tube');
        $connection = $input->getArgument('connection');
        
        $ignore_commands = array();
        if ($connection != 'feeds') {
            $ignore_commands = array(
                'stream:refresh'
            );
        }
        
        $output->writeln("<info>Starting worker for tube: {$tube} on connection {$connection}</info>");
        $memory = memory_get_usage(true) * 3;
        $startTime = time();
        while(1) {
            $job = $this->getContainer()->get('leezy.pheanstalk.'.$connection)
                ->watch($tube)
                ->ignore('default')
                ->reserve();
                
            
            $command = json_decode($job->getData());
            
            $command_name = strtolower(trim(pos(explode(" ",$command))));
            if (!in_array($command_name, $ignore_commands)) {
                $output->writeln("<info>Processing job: {$command}</info>");
                try {
                    $app = $this->getApplication()->find($command_name);    
                } catch (\Exception $e) {
                    $output->writeLn("<error>Unknown task {$command_name}</error>");
                    $this->getContainer()->get('leezy.pheanstalk.'.$connection)->delete($job);
                    return false;
                }
            
                try {
                    $app->run(new StringInput("$command"), $output);    
                    $output->writeln("<info>Finished job: {$command}</info>");
                } catch (\Exception $e) {
                    $output->writeLn("<error>$command failed</error>");
                }
                unset($app);
            }
                  
            $this->getContainer()->get('leezy.pheanstalk.'.$connection)->delete($job);
            
            //handled by god
            /*if ($memory < memory_get_usage(true)) {
                $output->writeln("<error>Worker is using too much RAM, dying</error>");
                exit(1);
            }*/
            $timeElapsed = time() - $startTime;
            if ($timeElapsed > 10*60) {
                $output->writeln("<info>Worker is working for 10 minutes -> dying</info>");
                exit(1);
            }
            usleep(10);
        }

    }

}
