<?php

namespace PW\NewsletterBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\HttpFoundation\Request;

class NewsletterSendCommand extends ContainerAwareCommand
{
    /**
     * @var NewsletterManager
     */
    protected $newsletterManager;

    /**
     * @var NewsletterEmailManager
     */
    protected $newsletterEmailManager;

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var
     */
    protected $alreadySentToUsers = array();

    protected $excludedEmails = array(
        'lsilwadi@hotmail.com'
    );

    

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('newsletter:send')
            ->addOption('start', null, InputOption::VALUE_OPTIONAL, 'Start point of the interval', false)
            ->addOption('end', null, InputOption::VALUE_OPTIONAL, 'End point of the interval',false)
            ->addOption('newsletter', null, InputOption::VALUE_OPTIONAL, 'Newsletter ID', false)
            ->addOption('resend', null, InputOption::VALUE_NONE, 'Resend to already sent users')
            ->setDescription('Sends all commited newsletters to their sent at dates or send specific newsletter manually through a job');
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

        //\MongoCursor::$timeout = -1;

        if($input->getOption('newsletter')) {
            $newsletter = $this->newsletterManager->getRepository()->findOneById($input->getOption('newsletter'));

            $this->alreadySentToUsers = $this->newsletterEmailManager->getRepository()->findUserIdsByNewsletter($newsletter);

            $start = $input->getOption('start');
            $end = $input->getOption('end');

            if($start && $end && $start <= $end) {
                $output->writeln("<info>Newsletter <".$newsletter->getSubject()."> is being sent".(($start && $end) ? ", for users from <".$start."> to <".$end."> interval" : "")."</info>");

                $range = range($start, $end);
                $send_to = 0;
                //searching for {"name": /^a/i}
                foreach($range as $char) {
                    $output->writeln("<info>Searching for users that start with ".$char.".</info>");
                    $users = $this->userManager->getRepository()->findByTypeAndStartLetter('user', 'justActive', $char)->getQuery()->execute();

                    foreach($users as $user) {
                        $send_to += $this->sendToUser($newsletter, $user, $input, $output);
                    }
                }

                $newsletter->setStatus('sent');
                //$newsletter->setSentTo($sent_to);
                $this->newsletterManager->flush();
            }
            else {
                $this->sendNewsletterToAll($newsletter, $input, $output);
            }
        }
        else {
            $newsletters = $this->newsletterManager->findAllForSending();

            if (count($newsletters) === 0) {
                $output->writeln("<info>No newsletter to process.</info>");
            }

            foreach ($newsletters as $newsletter) {
                $this->alreadySentToUsers = $this->newsletterEmailManager->getRepository()->findUserIdsByNewsletter($newsletter);

                $this->sendNewsletterToAll($newsletter, $input, $output);
            }
        }

        $finishedIn = time() - $startTime;

        $output->writeln("<info>Processed all in ".$finishedIn." seconds.</info>");
    }

    protected function sendToUser($newsletter, $user, InputInterface $input, OutputInterface $output, $direct = false)
    {
        if(!$input->getOption('resend') && in_array($user->getId(), $this->alreadySentToUsers)) {
            $output->writeln("<error>Newsletter already sent to email ".$user->getEmail()."..</error>");

            return;
        }

        if($user->getSettings()->getEmail()->isNotificationEnabled('newsletter') && $user->getIsActive() && !in_array($user->getEmail(), $this->excludedEmails) ) {
            
            if($direct) {
                if($this->newsletterManager->getMailer()->send($newsletter, $user)) {
                    $output->writeln("<info>Newsletter sent successfully to email ".$user->getEmail()." | ".$user->getName().".</info>");
                }
                else {
                    $output->writeln("<error>Newsletter could not be sent to email ".$user->getEmail()." due to mailer error.</error>");

                }
            } else {
                $command = "newsletter:send-one --userId={$user->getId()} --newsletter={$newsletter->getId()}";
                if($input->getOption('resend')) {
                    $command .= ' --resend=1';
                }
                
                $return = $this->getContainer()->get('pw.event')->requestJob($command, 'normal', 'newsletter', '', 'feeds');
                $output->writeln("<info>Newsletter queued email ".$user->getEmail()." | ".$user->getName().".</info>");    
            }    
            
            return 1;            
        }
        return 0;
    }

    protected function sendNewsletterToAll($newsletter, InputInterface $input, OutputInterface $output)
    {
        $users = $this->userManager->getRepository()->findByType('user', 'justActive', false)->getQuery()->execute();

        $output->writeln("<info>Newsletter <".$newsletter->getSubject()."> is being sent</info>");

        $i = 0;
        foreach($users as $user) {
           $this->sendToUser($newsletter, $user, $input, $output);
           if($i === 0) {
                $newsletter->setStatus('sedning');
                $this->newsletterManager->flush();
           } 
           $i++;
        }

        $newsletter->setStatus('sent');
        
        $this->newsletterManager->flush();
    }
}
