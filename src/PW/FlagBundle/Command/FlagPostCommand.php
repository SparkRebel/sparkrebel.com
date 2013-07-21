<?php

namespace PW\FlagBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

class FlagPostCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('flag:post')
            ->setDescription('Flag a Post')
            ->setDefinition(array(
                new InputArgument('postId', InputArgument::REQUIRED, 'Post id to flag'),
                new InputOption('reason', null, InputOption::VALUE_OPTIONAL, 'Reason for flagging')
            ))
            ->setHelp(PHP_EOL . $this->getDescription() . PHP_EOL);
    }

    /**
     * Initializes the command just after the input has been validated.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if (!in_array(PHP_OS, array('WINNT'))) {
            $output->setDecorated(true);
        }

        $output->writeln("");
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $dialog \Symfony\Component\Console\Helper\DialogHelper */
        $dialog = $this->getHelperSet()->get('dialog');

        /* @var $postManager \PW\PostBundle\Model\PostManager */
        $postManager = $this->getContainer()->get('pw_post.post_manager');
        $postId = $input->getArgument('postId');
        $post = $postManager->find($postId);

        $output->writeln('Flagging Post...');
        $output->writeln("<comment>ID:\t\t" . $postId . '</comment>');
        $output->writeln("<comment>Description:\t" . $post->getDescription() . '</comment>');
        $output->writeln("<comment>Source:\t\t" . $post->getLink() . '</comment>');
        $output->writeln("<comment>Created By:\t" . $post->getCreatedBy()->getName() . '</comment>');
        $output->writeln("<comment>User Type:\t" . $post->getUserType() . '</comment>');
        $output->writeln('');

        if (!$dialog->askConfirmation($output, '<question>Continue with this action (Y/n)?</question> ', false)) {
            return;
        }

        //
        // Reason
        if (!$reason = $input->getOption('reason')) {
            $output->writeln('');
            $reason = $dialog->ask($output, 'Enter the reason for flagging: ', null);
            $output->writeln('');
            if (empty($reason)) {
                throw new \InvalidArgumentException('You must enter a reason for flagging this post.');
            }
        }

        /* @var $flagManager \PW\FlagBundle\Model\FlagManager */
        $flagManager = $this->getContainer()->get('pw_flag.flag_manager');
        $flag = $flagManager->flagObject($post, $reason);

        $output->writeln('<info>Successfully flagged Post</info>');
    }
}
