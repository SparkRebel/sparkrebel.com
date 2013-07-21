<?php

namespace PW\ApplicationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PW\ApplicationBundle\Document\Email;

class MailProcessCommand extends ContainerAwareCommand
{
    /**
     * @var \PW\ApplicationBundle\Model\EmailManager
     */
    protected $emailManager;

    protected function configure()
    {
        $this
            ->setName('mail:process')
            ->setDescription('Process an Email by id')
            ->setDefinition(array(
                new InputArgument('emailId', InputArgument::REQUIRED, 'The Email ID to process')
            ));
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
        // Setup host
        $host = $this->getContainer()->getParameter('host');
        $this->getContainer()->get('router')->getContext()->setHost($host);
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $emailId = $input->getArgument('emailId');
        $email   = $this->getEmailManager()->find($emailId);
        if (!$email) {
            throw new \RuntimeException("Email does not exist with ID: {$emailId}");
        } elseif ($email->getDeleted()) {
            throw new \RuntimeException("Email has been removed: {$emailId}");
        }

        if ($result = $this->processEmail($email)) {
            $output->writeln("<info>Email was successfully added to spool: {$emailId}</info>");
            $this->getEmailManager()->delete($email, null, false);
        } else {
            $output->writeln("<error>Email failed being added to spool: {$emailId}</error>");
        }
    }

    /**
     * @param \PW\ApplicationBundle\Document\Email $email
     * @return int
     * @throws \RuntimeException
     */
    protected function processEmail(Email $email)
    {
        switch ($email->getType()) {
            case 'notifications':
                return $this->getContainer()->get('pw_activity.mailer')->sendNotificationsEmailMessage($email);
                break;
            default:
                throw new \RuntimeException("No handler registered for Email type: {$email->getType()}");
                break;
        }
    }

    /**
     * @return \PW\ApplicationBundle\Model\EmailManager
     */
    public function getEmailManager()
    {
        if ($this->emailManager == null) {
            $this->emailManager = $this->getContainer()->get('pw.email_manager');
        }

        return $this->emailManager;
    }
}
