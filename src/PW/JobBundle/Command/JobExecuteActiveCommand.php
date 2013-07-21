<?php

namespace PW\JobBundle\Command;

use PW\UserBundle\Document\Follow;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Execute all active Jobs. This command will be run by cronjob.
 */
class JobExecuteActiveCommand extends ContainerAwareCommand
{
    protected $dm;
    protected $output;

    protected function configure()
    {
        $this
            ->setName('getty-jobs:execute:active')
            ->setDescription('Execute all active Jobs (or jobId if given).')
            ->setDefinition(array(
                new InputArgument('jobId', InputArgument::OPTIONAL, 'The Job $id')
            ));
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
             
        $this->output->writeln("<info>[" . date('Y-m-d H:i:s') . "] starting command getty-jobs:execute:active</info>");
        
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        
        /* @var $jobManager \PW\JobBundle\Model\JobManager */
        $jobManager = $this->getContainer()->get('pw_job.job_manager');
        $eventManager = $this->getContainer()->get('pw.event');

        $jobId = $input->getArgument('jobId');
        $jobs = array();
        if (!$jobId) {
            $jobs = $jobManager->findAllActiveAndRunning();
            $count = count($jobs);
            $this->output->writeln("<info>Found {$count} active and running Jobs</info>");
        } else {
            $job = $jobManager->find(new \MongoId($jobId));
            if ($job) {
                $jobs[] = $job;
                $this->output->writeln("<info>Found Job {$jobId}</info>");
            } else {
                $this->output->writeln("<error>Job {$jobId} not found</error>");
            }
        }
        
        if (count($jobs)<1) return false;
        $this->output->writeln("<info>Executing...</info>");
        
        foreach ($jobs as $job) {
            $board = $job->getBoard();
            
            $cmd = $job->getCmd();
            
            /*
            // queue job to gearman
            $cmd = str_replace("{keywords}", addslashes($job->getKeywords()), $cmd);
            $cmd = str_replace("{collection}", addslashes($board->getName()), $cmd);
            $this->output->writeln("<info>Job {$job->getId()}: {$cmd}</info>");
            $eventManager->requestJob($cmd);
            */
            
            // run job now (without gearman)
            $parts = explode(" ", trim($cmd));
            $commandName = $parts[0]; // getty:query
            $command     = $this->getApplication()->find($commandName);            
            $params = array(
                'command' => $commandName,
                'keywords' => $job->getKeywords(),
                'board' => $board->getName(),
                '--env'=>'prod',
                //'--limit' => 200,
                //'--reset'=>true
            );            
            $command->run(new \Symfony\Component\Console\Input\ArrayInput($params), $output);
            
            // activete target collection
            if (!$board->getIsActive()) {
                $board->setIsActive(true);
                $this->dm->persist($board);
                $this->dm->flush();
            }
        }
        
        $this->output->writeln("<info>[" . date('Y-m-d H:i:s') . "] ending command getty-jobs:execute:active</info>");
    }
    
}
