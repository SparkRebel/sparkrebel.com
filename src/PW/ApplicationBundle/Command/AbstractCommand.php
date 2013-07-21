<?php

namespace PW\ApplicationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends ContainerAwareCommand
{
    protected $dm;
    protected $userManager;
    protected $followManager;
    protected $boardManager;
    protected $postManager;
    protected $eventManager;
    protected $assetManager;

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     * @return \PW\UserBundle\Document\User
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function getUser(InputInterface $input, OutputInterface $output)
    {
        /* @var $dialog \Symfony\Component\Console\Helper\DialogHelper */
        $dialog = $this->getHelperSet()->get('dialog');

        if (!$userId = $input->getOption('id')) {
            $userId = false;
        }

        if (!$userId && !$userEmail = $input->getOption('email')) {
            $userEmail = false;
        }

        if (!$userId && !$userEmail) {
            $output->writeln('');
            $userEmail = $dialog->ask($output, '<question>Enter e-mail of User to mock followers for:</question> ', null);
            if (empty($userEmail)) {
                throw new \InvalidArgumentException('User e-mail address is required');
            }
        }

        $user = false;
        if ($userId) {
            $user = $this->getUserManager()->getRepository()->find($userId);
            if (!$user) {
                throw new \RuntimeException('Unable to find User with ID: ' . $userId);
            }
        } else {
            if ($userEmail) {
                $user = $this->getUserManager()->findUserByEmail($userEmail);
                if (!$user) {
                    throw new \RuntimeException('Unable to find User with E-mail: ' . $userEmail);
                }
            }
        }

        return $user;
    }

    /**
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected function getDocumentManager()
    {
        if ($this->dm == null) {
            $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        }

        return $this->dm;
    }

    /**
     * @return \PW\UserBundle\Model\UserManager
     */
    protected function getUserManager()
    {
        if ($this->userManager == null) {
            $this->userManager = $this->getContainer()->get('pw_user.user_manager');
        }

        return $this->userManager;
    }

    /**
     * @return \PW\UserBundle\Model\FollowManager
     */
    protected function getFollowManager()
    {
        if ($this->followManager == null) {
            $this->followManager = $this->getContainer()->get('pw_user.follow_manager');
        }

        return $this->followManager;
    }

    /**
     * @return \PW\BoardBundle\Model\BoardManager
     */
    protected function getBoardManager()
    {
        if ($this->boardManager == null) {
            $this->boardManager = $this->getContainer()->get('pw_board.board_manager');
        }

        return $this->boardManager;
    }

    /**
     * @return \PW\PostBundle\Model\PostManager
     */
    protected function getPostManager()
    {
        if ($this->postManager == null) {
            $this->postManager = $this->getContainer()->get('pw_post.post_manager');
        }

        return $this->postManager;
    }

    /**
     * @return \PW\ApplicationBundle\Model\EventManager
     */
    protected function getEventManager()
    {
        if ($this->eventManager == null) {
            $this->eventManager = $this->getContainer()->get('pw.event');
        }

        return $this->eventManager;
    }

    /**
     * @return <type>
     */
    protected function getAssetManager()
    {
        if ($this->assetManager == null) {
            $this->assetManager = $this->getContainer()->get('pw.asset');
        }

        return $this->assetManager;
    }

    /**
     * @return \PW\ApplicationBundle\Console\Helper\ProgressHelper
     */
    protected function getProgressHelper()
    {
        $helperSet = $this->getHelperSet();
        if (!$helperSet->has('progress')) {
            $helperSet->set(new \PW\ApplicationBundle\Console\Helper\ProgressHelper());
        }

        return  $helperSet->get('progress');
    }
}
