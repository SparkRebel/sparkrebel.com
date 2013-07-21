<?php

namespace PW\CategoryBundle\Command;

use PW\ApplicationBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractAreaCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDefinition(array(
            new InputOption('id', null, InputOption::VALUE_REQUIRED, 'Area ID to update'),
            new InputOption('name', null, InputOption::VALUE_REQUIRED, 'Area Name to update'),
        ));
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     * @return \PW\CategoryBundle\Document\Area
     * @throws \InvalidArgumentException
     */
    protected function getArea(InputInterface $input, OutputInterface $output)
    {
        $areaId   = $input->getOption('id');
        $areaName = $input->getOption('name');

        if (!$areaId && !$areaName) {
            /* @var $dialog \Symfony\Component\Console\Helper\DialogHelper */
            $dialog = $this->getHelperSet()->get('dialog');
            $output->writeln('');
            $areaName = $dialog->ask($output, '<question>Enter name of Area to update:</question> ', null);
            if (empty($areaName)) {
                throw new \InvalidArgumentException('Area name is required');
            }
        }

        $area = false;
        if ($areaId) {
            if (!($area = $this->getAreaRepository()->find($areaId))) {
                throw new \InvalidArgumentException(sprintf('The source Area "%s" does not exist.', $source));
            }
        } else {
            if ($areaName) {
                if (!($area = $this->getAreaRepository()->findOneBy(array('name' => $areaName)))) {
                    throw new \InvalidArgumentException(sprintf('The source Area "%s" does not exist.', $source));
                }
            }
        }

        return $area;
    }

    protected function getAreaRepository()
    {
        return $this->getDocumentManager()->getRepository('PWCategoryBundle:Area');
    }
}
