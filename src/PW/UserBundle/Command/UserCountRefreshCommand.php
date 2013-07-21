<?php

namespace PW\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * (re)Process the counts associated with a User
 */
class UserCountRefreshCommand extends ContainerAwareCommand
{
    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('user:count:refresh')
            ->setDescription('(re)Process the counts associated with a User')
            ->setDefinition(array(
                new InputOption('id', null, InputOption::VALUE_REQUIRED, 'User ID to update'),
                new InputOption('email', null, InputOption::VALUE_REQUIRED, 'User Email to update'),
            ))
            ->setHelp(PHP_EOL . $this->getDescription() . PHP_EOL);
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $this->getUser($input, $output);

        $beforeCounts = $user->getCounts()->toArray();
        $this->getUserManager()->processCounts($user);
        $afterCounts = $user->getCounts()->toArray();

        $updated = false;
        $output->writeln('');

        $diff = array_diff_assoc($beforeCounts, $afterCounts);
        foreach ($diff as $key => $count) {
            $updated = true;
            $output->writeln("Updated <info>{$key}</info> from <info>{$beforeCounts[$key]}</info> to <info>{$afterCounts[$key]}</info>.");
        }

        if (!$updated) {
            $output->writeln('<comment>No counts updated.</comment>');
        }
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
            $userEmail = $dialog->ask($output, '<question>Enter e-mail of User:</question> ', null);
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

        return $user;
    }

    /**
     * @return \PW\UserBundle\Model\UserManager
     */
    protected function getUserManager()
    {
        return $this->getContainer()->get('pw_user.user_manager');
    }
}
