<?php

namespace PW\InviteBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

class AssignCodeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('invite:assign:code')
            ->setDescription('Assign an invite code to a user')
            ->setDefinition(array(
                new InputOption('id', null, InputOption::VALUE_REQUIRED, 'User ID to update'),
                new InputOption('email', null, InputOption::VALUE_REQUIRED, 'User Email to update'),
                new InputOption('generate', null, InputOption::VALUE_NONE, 'Automatically generate a code'),
                new InputOption('code', null, InputOption::VALUE_REQUIRED, 'Code to assign to user'),
                new InputOption('maxUses', null, InputOption::VALUE_REQUIRED, 'The maximum uses the invite code is allowed (0 for no maximum)', 0)
            ))
            ->setHelp(PHP_EOL . $this->getDescription() . PHP_EOL);
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     * @return \PW\UserBundle\Document\User
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function getUser(InputInterface $input, OutputInterface $output)
    {
        /* @var $dialog \Symfony\Component\Console\Helper\DialogHelper */
        $dialog = $this->getHelperSet()->get('dialog');

        if (!$userId = $input->getOption('id')) {
            $userId = false;
        }

        if (!$userId && !$userEmail = $input->getOption('email')) {
            $userEmail = false;
        }

        if (!$userId && !$userEmail) {
            $output->writeln('');
            $userEmail = $dialog->ask($output, '<question>Enter e-mail of User to assign code:</question> ', null);
            if (empty($userEmail)) {
                throw new \InvalidArgumentException('User e-mail address is required');
            }
        }

        $user = false;
        if ($userId) {
            $user = $this->getUserManager()->getRepository()->find($userId);
            if (!$user) {
                throw new \RuntimeException('Unable to find User with ID: ' . $userId);
            }
        } else {
            if ($userEmail) {
                $user = $this->getUserManager()->findUserByEmail($userEmail);
                if (!$user) {
                    throw new \RuntimeException('Unable to find User with E-mail: ' . $userEmail);
                }
            }
        }

        if ($user->getUserType() !== 'user') {
            throw new \RuntimeException('Cannot assign inviteCode to User with type: ' . $user->getUserType());
        }

        if ($user->getAssignedInviteCode()) {
            throw new \RuntimeException('User already has an invite code assigned: ' . $user->getAssignedInviteCode()->getValue());
        }

        return $user;
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     * @return \PW\InviteBundle\Document\Code
     */
    protected function getCode(InputInterface $input, OutputInterface $output)
    {
        /* @var $dialog \Symfony\Component\Console\Helper\DialogHelper */
        $dialog = $this->getHelperSet()->get('dialog');

        $code = false;
        $multiple = false;

        try {
            if (!$input->getOption('generate') && !$code = $input->getOption('code')) {
                $output->writeln('');
                $output->writeln("If you do not enter a code below, <comment>one will be generated</comment>.");
                $output->writeln("If you enter a code that does not exist, <comment>it will be created for you</comment>.");
                $code = $dialog->ask($output, "<question>Enter the code to assign:</question> ", null);
            }
        } catch (\InvalidArgumentException $e) {
            $multiple = true;
        }

        $maxUses = (int) $input->getOption('maxUses');
        if (empty($code)) {
            if (!$multiple) {
                $output->writeln('');
                $output->writeln("Generating code with " . (!$maxUses ? '<error>unlimited</error>' : $maxUses) . " maximum uses allowed...");
            }
            $code = $this->getCodeManager()->createRandom($maxUses);
        } else {
            $codeValue = $code;
            $code = $this->getCodeManager()->findByValue($codeValue);
            if (!$code) {
                $code = $this->getCodeManager()->create(array(
                    'maxUses' => $maxUses,
                    'value'   => $code,
                ));
            }
        }

        return $code;
    }

    /**
     * @param \PW\InviteBundle\Document\Code $code
     * @param \PW\UserBundle\Document\User $user
     */
    protected function assignCode(\PW\InviteBundle\Document\Code $code, \PW\UserBundle\Document\User $user)
    {
        $code->setAssignedUser($user);
        $user->setAssignedInviteCode($code);
        $this->getCodeManager()->update($code, false);
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $this->getUser($input, $output);
        $code = $this->getCode($input, $output);
        $this->assignCode($code, $user);

        $this->getCodeManager()->flush();

        $output->writeln('');
        $output->writeln("Assigned code <info>'{$code->getValue()}'</info> to User <info>'{$user->getId()}'</info>...");
    }

    /**
     * @return \PW\UserBundle\Model\UserManager
     */
    protected function getUserManager()
    {
        return $this->getContainer()->get('pw_user.user_manager');
    }

    /**
     * @return \PW\InviteBundle\Model\CodeManager
     */
    protected function getCodeManager()
    {
        return $this->getContainer()->get('pw_invite.code_manager');
    }
}
