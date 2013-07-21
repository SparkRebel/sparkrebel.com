<?php

namespace PW\InviteBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

class AssignCodesCommand extends AssignCodeCommand
{
    protected function configure()
    {
        $this
            ->setName('invite:assign:codes')
            ->setDescription('Assigns an invite code to all users')
            ->setDefinition(array(
                new InputOption('generate', null, InputOption::VALUE_NONE, 'Automatically generate a code'),
                new InputOption('maxUses', null, InputOption::VALUE_REQUIRED, 'The maximum uses the invite codes are allowed (0 for no maximum)', 0)
            ))
            ->setHelp(PHP_EOL . $this->getDescription() . PHP_EOL);
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $dialog \Symfony\Component\Console\Helper\DialogHelper */
        $dialog = $this->getHelperSet()->get('dialog');

        $users = $this->getUserManager()
            ->getRepository()
            ->createQueryBuilder()
            ->field('type')->equals('user')
            ->field('isActive')->equals(true)
            ->getQuery()->execute();

        $maxUses = (int) $input->getOption('maxUses');
        if (!$maxUses) {
            $output->writeln('');
            $output->writeln('Using this command without <comment>--maxUses</comment> will generate codes that have <error>unlimited</error> uses...');
            if (!$dialog->askConfirmation($output, "<question>Are you sure you want to proceed Y/(n)?</question> ", false)) {
                exit;
            }
        }

        $count = 0;
        foreach ($users as $user /* @var $user \PW\UserBundle\Document\User */) {
            if (!$user->getAssignedInviteCode()) {
                $count++;

                $code = $this->getCode($input, $output);
                $this->assignCode($code, $user);

                if ($count % 100 === 0) {
                    $this->getCodeManager()->flush();
                }
            }
        }

        $this->getCodeManager()->flush();

        $output->writeln('');
        $output->writeln("Assigned random codes to {$count} Users...");
    }
}
