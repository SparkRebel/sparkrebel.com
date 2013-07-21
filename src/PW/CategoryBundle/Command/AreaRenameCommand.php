<?php

namespace PW\CategoryBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AreaRenameCommand extends AbstractAreaCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('area:rename')
            ->setDescription('Rename an Area record')
            ->addOption('new_name', null, InputOption::VALUE_REQUIRED, 'New Area Name')
        ;
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $area     = $this->getArea($input, $output);
        $areaName = $input->getArgument('new_name');

        if (!$areaName) {
            /* @var $dialog \Symfony\Component\Console\Helper\DialogHelper */
            $dialog = $this->getHelperSet()->get('dialog');
            $output->writeln('');
            $areaName = $dialog->ask($output, '<question>Enter *new* name of Area:</question> ', null);
            if (empty($areaName)) {
                throw new \InvalidArgumentException('New Area name is required');
            }
        }

        $area->setName($areaName);
        $this->getDocumentManager()->persist($area);
        $this->getDocumentManager()->flush();
    }
}
