<?php

namespace PW\PicScoutBundle\Mailer;

use FOS\UserBundle\Mailer\MailerInterface;
use PW\ApplicationBundle\Mailer\Mailer as BaseMailer;
use PW\UserBundle\Document\User;
use PW\PostBundle\Document\Post;

class Mailer extends BaseMailer
{
    /**
     * sends and email when piscout and getty call said that we cant use this in our service.
     * 
     * @param \PW\PostBundle\Document\Post $post
     */

    public function sendAssetDeletedNotificationForPost(Post $post)
    {
    	$user = $post->getCreatedBy();

        $template = $this->parameters['template']['welcome'];
        $context  = array('user' => $user, 'post' => $post);
        $from     = $this->parameters['from_email']['welcome'];
        $to       = array($user->getEmail() => $user->getName());

        return $this->sendMessage($template, $context, $from, $to);
    }

}