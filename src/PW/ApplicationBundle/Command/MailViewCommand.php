<?php

namespace PW\ApplicationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MailViewCommand extends ContainerAwareCommand
{
    /**
     * @var \PW\ApplicationBundle\Model\EmailManager
     */
    protected $emailManager;

    protected function configure()
    {
        $this
            ->setName('mail:view')
            ->setDescription('Process an Email by id')
            ->setDefinition(array(
                new InputArgument('type', InputArgument::REQUIRED, 'The Email type'),
                new InputOption('output', '-o', InputOption::VALUE_REQUIRED, 'text|html', 'html'),
            ));
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Setup host
        $host = $this->getContainer()->getParameter('host');
        $this->getContainer()->get('router')->getContext()->setHost($host);

        $user = $this->getContainer()->get('pw_user.user_manager')->getRepository()->findOneBy(array());
        $type = $input->getArgument('type');
        switch ($type) {
            case 'welcome':
                $template = $this->getContainer()->get('twig')->loadTemplate('PWUserBundle:Register:welcome.email.twig');
                $context  = compact('user');
                break;
            case 'activity':
                $notifications = $this->getContainer()->get('pw_activity.notification_manager')->getRepository()->createQueryBuilder()->eagerCursor(true)->limit(5)->getQuery()->execute();
                $count = $this->getContainer()->get('pw_activity.notification_manager')->getRepository()->createQueryBuilder()->count()->getQuery()->execute();
                $template = $this->getContainer()->get('twig')->loadTemplate('PWActivityBundle:Notification:summary.email.twig');
                $context  = compact('user', 'notifications', 'count');
                break;
            default:
                throw new \RuntimeException("Unknown mail type: {$type}");
                break;
        }

        $subject  = $template->renderBlock('subject', $context);
        $bodyHtml = $template->renderBlock('body_html', array_merge($context, array('subject' => $subject)));
        $bodyText = $template->renderBlock('body_text', array_merge($context, array('subject' => $subject)));

        if ($input->getOption('output') == 'text') {
            if (empty($bodyText)) {
                $bodyText = strip_tags($bodyHtml);
            }
            $output->write($bodyText);
        } else {
            $output->write($bodyHtml);
        }
        $output->writeln('');
        $output->writeln("<info>Subject:</info> {$subject}");
    }
}
