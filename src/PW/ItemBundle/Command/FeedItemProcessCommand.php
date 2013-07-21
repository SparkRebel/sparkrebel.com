<?php

namespace PW\ItemBundle\Command;

use PW\ApplicationBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Process one specific feed_items entry
 */
class FeedItemProcessCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('feed:item:process')
            ->setDescription('Run all steps for one item')
            ->setDefinition(array(
                new InputArgument('id', InputArgument::REQUIRED, 'The feed-item fid')
            ));
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        foreach (range(1, 3) as $i) {
            $commandName = "feed:item:step$i";
            $command     = $this->getApplication()->find($commandName);
            $returnCode = $command->run(new ArrayInput(array(
                'command' => $commandName,
                'id' => $id
            )), $output);
            $output->writeln("<info>return code for step ".$i.": ".$returnCode."</info>");
            if ($returnCode == -1 || $returnCode === false) {
                break;
            }
        }
    }
}
