<?php

namespace PW\MailerBundle\Mailer;

use PW\ApplicationBundle\Mailer\Mailer as BaseMailer;
use PW\UserBundle\Document\User;

class Mailer extends BaseMailer
{
    const UNFINISHED_SIGNUP = 'unfinished_signup';
    const MISS_YOU = 'miss_you';
    /**
     * Sends miss you email
     * 
     * @param \PW\UserBundle\Document\User $user
     */

    public function sendMissYouEmail(User $user)
    {

        $template = $this->parameters['miss_you_email']['template'];
        $context  = array('user' => $user);
        $from     = $this->parameters['miss_you_email']['from_email'];
        $to       = array($user->getEmail() => $user->getName());

        return $this->sendMessage($template, $context, $from, $to);
    }


    /**
     * Sends unfinished registration email
     * 
     * @param \PW\UserBundle\Document\User $user
     */

    public function sendUnfinishedSignupEmail(User $user)
    {        

        $template = $this->parameters['singup_email']['template'];
        $context  = array('user' => $user);
        $from     = $this->parameters['singup_email']['from_email'];
        $to       = array($user->getEmail() => $user->getName());
        return $this->sendMessage($template, $context, $from, $to);
    }

}