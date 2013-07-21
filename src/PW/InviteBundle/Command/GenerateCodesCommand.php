<?php

namespace PW\InviteBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

class GenerateCodesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('invite:generate')
            ->setDescription('Generate random invite codes')
            ->setDefinition(array(
                new InputArgument('total', InputArgument::REQUIRED, 'Total invite codes to generate'),
                new InputArgument('maxUses', InputArgument::OPTIONAL, 'The maximum uses the invite codes are allowed (0 for no maximum)', 0)
            ))
            ->setHelp(PHP_EOL . $this->getDescription() . PHP_EOL);
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $total   = (int) $input->getArgument('total');
        $maxUses = (int) $input->getArgument('maxUses');

        /* @var $codeManager \PW\InviteBundle\Model\CodeManager */
        $codeManager = $this->getContainer()->get('pw_invite.code_manager');
        $codeManager->generate($total, $maxUses);

        $output->writeln(sprintf('<info>Total invite codes generated: %d</info>', $total));
    }
}
