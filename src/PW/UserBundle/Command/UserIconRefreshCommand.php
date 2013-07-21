<?php

namespace PW\UserBundle\Command;

use PW\ApplicationBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserIconRefreshCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('user:icon:refresh')
            ->setDescription("(Re)request a user\'s Facebook icon and copy to our assets")
            ->setDefinition(array(
                new InputArgument('userId', InputArgument::REQUIRED, 'User ID to fetch for')
            ));
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userId = $input->getArgument('userId');
        $user   = $this->getUserManager()->getRepository()->find($userId);
        if (!$user) {
            throw new \RuntimeException('Unable to find User with ID: ' . $userId);
        }

        if ($this->getUserManager()->refreshFacebookIcon($user)) {
            $output->writeln('');
            $output->writeln("<info>Updated User's profile image based on:</info> {$user->getIcon()->getUrl()}");
        } else {
            $output->writeln('');
            $output->writeln("<error>Could not update User's profile image</error>");
        }
    }
}
