<?php

namespace PW\NewsletterBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\HttpFoundation\Request;

class NewsletterSendOneCommand extends NewsletterSendCommand
{
    

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('newsletter:send-one')
            ->addOption('userId', null, InputOption::VALUE_REQUIRED, 'User ID')            
            ->addOption('newsletter', null, InputOption::VALUE_REQUIRED, 'Newsletter ID')    
            ->addOption('resend', null, InputOption::VALUE_NONE, 'Resend to already sent users')        
            ->setDescription('Sends one newsletter to user');
    }

    /**
     * Initializes the command just after the input has been validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->enterScope('request');
        $this->getContainer()->set('request', new Request(), 'request');

        // Setup host
        $host = $this->getContainer()->getParameter('host');
        $this->getContainer()->get('router')->getContext()->setHost($host);
    }

    /**
     * execute
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startTime = time();

        $this->newsletterManager = $this->getContainer()->get('pw_newsletter.newsletter_manager');
        $this->newsletterEmailManager = $this->getContainer()->get('pw_newsletter.newsletter_email_manager');
        $this->userManager = $this->getContainer()->get('pw_user.user_manager');

        $newsletter = $this->newsletterManager->getRepository()->findOneById($input->getOption('newsletter'));
        $user = $this->userManager->getRepository()->findOneById($input->getOption('userId'));


        $this->sendToUser($newsletter, $user, $input, $output, true);
               
        $finishedIn = time() - $startTime;
        $output->writeln("<info>Processed one newsletter in ".$finishedIn." seconds.</info>");
    }

  
}
