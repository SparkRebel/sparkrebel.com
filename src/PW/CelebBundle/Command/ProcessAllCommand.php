<?php

namespace PW\CelebBundle\Command;

use PW\ApplicationBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PW\BoardBundle\Document\Board;
use PW\AssetBundle\Document\Asset;
use PW\CelebBundle\GettyImage;
use Symfony\Component\Console\Input\InputOption;

class ProcessAllCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('celeb:process:all')
            ->setDescription('Fetch images for all celebrities')
            ->setDefinition(array(
                new InputOption('reset', null, InputOption::VALUE_NONE, 'Rebuild all celebs')
            ));
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reset  = $input->getOption('reset');
        
        $dm   = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $user = $dm->getRepository('PWUserBundle:User')->findOneByName('Celebs');
        if (!$user) {
            $this->getApplication()->find('celeb:create:user')->run(new ArrayInput(array()), $output);
        }

        $boards = $this->getBoardManager()->getRepository()
            ->findByUser($user)
            ->getQuery()
            ->execute();

        $progress = $this->getProgressHelper();
        $progress->start($output, $boards->count());

        foreach ($boards as $board) {
            $jobCmd = 'celeb:process ' . escapeshellarg($board->getName());
            if ($reset) {
                $jobCmd .= ' --reset';
            }
            $this->getEventManager()->requestJob($jobCmd, 'low');
            $progress->advance();
        }

        $progress->finish();

        $output->writeln('');
        $output->writeln("<info>Finished processing {$boards->count()} items...</info>");
    }
}
