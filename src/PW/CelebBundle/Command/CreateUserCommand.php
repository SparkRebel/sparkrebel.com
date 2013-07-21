<?php

namespace PW\CelebBundle\Command;

use PW\ApplicationBundle\Command\AbstractCommand;
use PW\UserBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates celebs user for managing celebs
 */
class CreateUserCommand extends AbstractCommand
{
    protected $references = array();

    protected function configure()
    {
        $this
            ->setName('celeb:create:user')
            ->setDescription('Creates the Celebs User record')
        ;
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($celebUser = $this->getUserManager()->getRepository()->findOneByName('Celebs')) {
            $output->writeln("<info>Celebs user already exists:</info> {$celebUser->getId()}");
        } else {
            $celebUser = $this->addCelebsUser();
            $output->writeln("<info>Celebs user has been created successfully:</info>  {$celebUser->getId()}");
        }

        $asset = $this->getAssetManager()->addImage('/images/celebs_icons/celeb.png');
        $celebUser->setIcon($asset);
        $this->getUserManager()->updateUser($celebUser);
        $output->writeln("<info>Added icon for Celebs user.</info>");
    }

    protected function addCelebsUser()
    {
    	$user = new User();
    	$user->setName('Celebs');
        $user->setUsername('Celebs');
    	$user->setEmail('celebs@sparkrebel.com');
        $user->setEnabled(true);
        $user->setDisabledNotifications(true);
        $this->getUserManager()->updateUser($user);
        return $user;
    }
}
