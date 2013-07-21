<?php

namespace PW\MailerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;    

use Symfony\Component\HttpFoundation\Request;

class UnfinishedSignupAllCommand extends ContainerAwareCommand
{
    
    protected function configure()
    {
        $this
            ->setName('pw:mailer:send-unfinished-signup:all')
            ->setDescription('Sends unfinised email for all matching users');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->enterScope('request');
        $this->getContainer()->set('request', new Request(), 'request');

        // Setup host
        $host = $this->getContainer()->getParameter('host');
        $this->getContainer()->get('router')->getContext()->setHost($host);
    }

    /**
     *
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        
        $userManager = $this->getContainer()->get('pw_user.user_manager');       
            
        $users = $userManager->getRepository()
            ->createQueryBuilder()
            ->field('settings.signupPreferences.areas')->exists(false) #we need only area check here            
            ->field('mailingsSent.' . \PW\MailerBundle\Mailer\Mailer::UNFINISHED_SIGNUP)->exists(false)
            ->getQuery()->execute();
        
        foreach ($users as $user) {
            $this->getContainer()->get('pw.event')->requestJob('pw:mailer:send-unfinished-signup ' . $user->getId());
        }
                           
        $output->writeln("<info>Command finished</info>");
        
    }
    
}



