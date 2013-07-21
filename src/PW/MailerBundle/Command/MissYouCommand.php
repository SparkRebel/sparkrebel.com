<?php

namespace PW\MailerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;    

use Symfony\Component\HttpFoundation\Request;

class MissYouCommand extends ContainerAwareCommand
{
    protected $mailer;

    protected function configure()
    {
        $this
            ->setName('pw:mailer:send-miss-you')
            ->setDescription('Sends unfinised email for one user')
            ->setDefinition(array(                
                new InputArgument('userId', InputArgument::REQUIRED)
            ));
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
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $userId = $input->getArgument('userId');

        $user   = $this->getContainer()->get('pw_user.user_manager')->getRepository()->find($userId);
        if (!$user) {
            throw new \RuntimeException('Unable to find User with ID: ' . $userId);
        }        

        $this->getContainer()->get('pw.global_mailer')->sendMissYouEmail($user);

        $output->writeln("<info>Sent miss you email for {$user->getName()}</info>");

        $user->updateMailing(\PW\MailerBundle\Mailer\Mailer::MISS_YOU);
        $this->dm->persist($user);
        $this->dm->flush();
    }
        
}
