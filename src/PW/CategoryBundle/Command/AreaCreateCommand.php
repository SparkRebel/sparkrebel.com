<?php

namespace PW\CategoryBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PW\CategoryBundle\Document\Area;

class AreaCreateCommand extends AbstractAreaCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        parent::configure();

        // ID Options not used
        $options = $this->getDefinition()->getOptions();
        unset($options['id']);

        $this
            ->setName('area:create')
            ->setDescription('Create a new Area record')
            ->getDefinition()->setOptions($options)
        ;
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
        {
        try {
            $area = $this->getArea($input, $output);
        } catch (\InvalidArgumentException $e) {
            // Not found is good
            $area = false;
        }

        if ($area) {
            throw new \InvalidArgumentException(sprintf('Area with name "%s" already exists.', $area->getName()));
        }

        $areaName = $input->getOption('name');
        if (!$areaName) {
            /* @var $dialog \Symfony\Component\Console\Helper\DialogHelper */
            $dialog = $this->getHelperSet()->get('dialog');
            $output->writeln('');
            $areaName = $dialog->ask($output, '<question>Enter name for *new* Area:</question> ', null);
            if (empty($areaName)) {
                throw new \InvalidArgumentException('Name for new Area is required');
            }
        }

        $area = new Area();
        $area->setName($areaName);
        $this->getDocumentManager()->persist($area);
        $this->getDocumentManager()->flush();
    }
}
