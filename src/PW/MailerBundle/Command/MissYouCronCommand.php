<?php

namespace PW\MailerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface,
    PW\MailerBundle\Mailer\Mailer;    

use Symfony\Component\HttpFoundation\Request;

class MissYouCronCommand extends ContainerAwareCommand
{
    
    protected $dateInterval = 30;

    protected function configure()
    {
        $this
            ->setName('pw:mailer:send-miss-you:all')
            ->setDescription('Sends miss You all to users who didnt visit site in 30 days');
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
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $d = new \DateTime(); 
        $d->modify("-{$this->dateInterval} day");
        $date = new \MongoDate($d->getTimestamp());

        
        $userManager = $this->getContainer()->get('pw_user.user_manager');       
        
        
        $qb = $userManager->getRepository()
            ->createQueryBuilder();
        $users = $qb
            ->addOr(
                # mailing was sent and it was sent more then n:interval days ago AND user did not login after getting mail
                $qb->expr()
                ->field('mailingsSent.' . Mailer::MISS_YOU)->exists(true)
                ->field('mailingsSent.' . Mailer::MISS_YOU)->lte($date)     
                ->where("function() { return this.lastLogin < this.mailingsSent." . Mailer::MISS_YOU . " }")           

            )
            ->addOr(
                # mailing wasnt sent and user last login was more than n:interval days ago
                $qb->expr()
                ->field('mailingsSent.' . Mailer::MISS_YOU)->exists(false)
                ->field('lastLogin')->lte($date)
            )
            ->getQuery()->execute();
        
        foreach ($users as $user) {
            $output->writeln("<info>Sending to {$user->getName()}</info>");
            $this->getContainer()->get('pw.event')->requestJob('pw:mailer:send-miss-you ' . $user->getId());
        }
                           
        $output->writeln("<info>Command finished</info>");
        
    }
    
}



