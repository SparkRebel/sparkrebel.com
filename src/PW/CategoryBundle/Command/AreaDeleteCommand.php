<?php

namespace PW\CategoryBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AreaDeleteCommand extends AbstractAreaCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('area:delete')
            ->setDescription('Delete an Area record')
        ;
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $area = $this->getArea($input, $output);

        $area->setDeleted(new \DateTime());
        $area->setIsActive(false);
        $this->getDocumentManager()->persist($area);
        $this->getDocumentManager()->flush();
    }
}
