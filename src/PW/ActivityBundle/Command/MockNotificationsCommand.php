<?php

namespace PW\ActivityBundle\Command;

use PW\ApplicationBundle\Command\AbstractMockCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PW\ActivityBundle\Document\Notification;

class MockNotificationsCommand extends MockActivityCommand
{
    protected function configure()
    {
        AbstractMockCommand::configure();
        $this->setName('mock:notifications')
             ->setDescription('Generate random notifications for a User')
             ->addOption('type', '-t', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Type to generate', array(
                 'post.create', 'post.repost', 'comment.create', 'comment.reply', 'user.follow',
             ));
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $notificationManager \PW\ActivityBundle\Model\NotificationManager */
        $notificationManager = $this->getNotificationManager();

        $tries = 0;
        $count = 0;
        $user  = $this->getUser($input, $output);
        $total = $input->getOption('total');
        $types = $input->getOption('type');

        /* @var $email \PW\ApplicationBundle\Document\Email */
        $email = $this->getContainer()->get('pw.email_manager')
            ->getRepository()
            ->findByUserAndType($user, 'notifications')
            ->getQuery()->getSingleResult();

        dd($email->getNotifications()->count());

        $notifications = $notificationManager->getRepository()
            ->createQueryBuilder()
            ->eagerCursor(false)
            ->field('type')->in($types)
            ->field('category')->equals('user')
            ->getQuery()->execute();

        $output->writeln('');
        foreach ($notifications as $notification /* @var $notification \PW\ActivityBundle\Document\Notification */) {
            $tries++;
            $randomType = array_rand($types);
            if ($notification->getType() == $randomType) {
                if ($notification->getUser() && $notification->getUser()->getId() == $user->getId()) {
                    $output->writeln('<comment>Skipping notification with User self...</comment>');
                    continue;
                }

                $new = new Notification();
                if ($notification->getUser()) {
                    $new->setUser($notification->getUser());
                }
                $new->setTarget($notification->getTarget());
                $new->setType($notification->getType());
                $notificationManager->save($new, array('validate' => false));
                $count++;

                if ($count === $total) {
                    $output->writeln('');
                    $output->writeln("");
                    break;
                }
            }
        }

        if ($count !== $total) {
            $output->writeln('');
            $output->writeln("<error>Unable to generate {$total} notifications. Generated: {$count} - Tried: {$tries}</error>");
        }
    }


    /**
     * @return \PW\ActivityBundle\Model\NotificationManager
     */
    protected function getNotificationManager()
    {
        return $this->getContainer()->get('pw_activity.notification_manager');
    }
}
