<?php

namespace PW\UserBundle\Mailer;

use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use PW\ApplicationBundle\Mailer\Mailer as BaseMailer;
use PW\UserBundle\Document\User;

class Mailer extends BaseMailer implements MailerInterface
{
    /**
     * Send an email to welcome a new user
     * 
     * @param \PW\UserBundle\Document\User $user
     */
    public function sendWelcomeEmailMessage(User $user)
    {
        $template = $this->parameters['template']['welcome'];
        $context  = array('user' => $user);
        $from     = $this->parameters['from_email']['welcome'];
        $to       = array($user->getEmail() => $user->getName());

        return $this->sendMessage($template, $context, $from, $to);
    }

    /**
     * Send an email to a user to confirm the account creation
     *
     * @param UserInterface $user
     */
    public function sendConfirmationEmailMessage(UserInterface $user)
    {
    }

    /**
     * Send an email to a user to confirm the password reset
     *
     * @param UserInterface $user
     */
    public function sendResettingEmailMessage(UserInterface $user)
    {
    }
}