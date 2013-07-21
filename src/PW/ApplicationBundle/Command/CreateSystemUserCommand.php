<?php

namespace PW\ApplicationBundle\Command;

use PW\UserBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates system SparkRebel user
 */
class CreateSystemUserCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('sr:create:user')
            ->setDescription('Creates the SparkRebel User record')
        ;
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $this->getContainer()->getParameter('pw.system_user.sparkrebel.username');
        if ($user = $this->getUserManager()->getRepository()->findOneByUsername($username)) {
            $output->writeln("<info>System User already exists:</info> {$user->getId()}");
        } else {
            $user = new User();
            $user->setName($this->getContainer()->getParameter('pw.system_user.sparkrebel.name'));
            $user->setUsername($this->getContainer()->getParameter('pw.system_user.sparkrebel.username'));
            $user->setEmail($this->getContainer()->getParameter('pw.system_user.sparkrebel.email'));
            $user->setEnabled(true);
            $user->setDisabledNotifications(true);
            $this->getUserManager()->update($user, false);
            $output->writeln("<info>System User has been created successfully:</info> {$user->getId()}");
        }

        $asset = $this->getAssetManager()->addImage('/images/users/sparkrebel.png');
        $user->setIcon($asset);
        $this->getUserManager()->update($user);
        $output->writeln("<info>Added icon for System user.</info>");
    }
}
