<?php

namespace PW\ApplicationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;

class MailProcessAllCommand extends MailProcessCommand
{
    protected function configure()
    {
        $this
            ->setName('mail:process:all')
            ->setDescription('Process all emails scheduled to be sent before right-now');
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $command = 'email:fix-invalid-references';
        $app = $this->getApplication()->find($command);    
                                            
        $app->run(new StringInput("$command"), $output);    
        $output->writeln("<info>Cleaning up: {$command}</info>");
        sleep(5);
        $emails = $this->getEmailManager()->getRepository()
            ->createQueryBuilderWithOptions()
            ->field('user')->prime(true)
            ->field('scheduledDate')->lte(new \DateTime())
            ->sort('scheduledDate', 'asc')
            ->getQuery()->execute();

        $count = 0;
        foreach ($emails as $email /* @var $email \PW\ApplicationBundle\Document\Email */) {
            if ($input->getOption('verbose')) {
                $output->writeln("<question>{$count}:</question> {$email->getId()}");
            }
            $user = $email->getUser();
            if (!$user || $user->getDeleted()) {
                $output->writeln("Skipping <comment>{$email->getId()}</comment> - User Deleted...");
                $count++;
                $this->getEmailManager()->delete($email, null, false, false);
                continue;
            }
            try {
                if ($result = $this->processEmail($email)) {
                    $output->writeln("Email was successfully added to spool: <info>{$email->getId()}</info> - <info>{$user->getEmail()}</info>");
                    $count++;
                    $this->getEmailManager()->delete($email, null, false, false);
                    if ($count % 10 === 0) {
                        $this->getEmailManager()->flush();
                    }
                } else {
                    $output->writeln("<error>Email failed being added to spool: {$email->getId()}</error>");
                }
            } catch (\Exception $e) {
                $output->writeln("<error>{$e->getMessage()}</error>");
            }
        }

        $output->writeln('');
        if ($count) {
            $this->getEmailManager()->flush();
            $output->writeln("Processed <info>{$count}</info> total mails...");
        } else {
            $output->writeln('<comment>No mail to process...</comment>');
        }
    }
}
