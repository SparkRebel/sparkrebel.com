<?php

namespace PW\NewsletterBundle\Model;

use PW\ApplicationBundle\Model\AbstractManager,
    PW\NewsletterBundle\Document\NewsletterEmail,
    PW\UserBundle\Document\User;

class NewsletterEmailManager extends AbstractManager
{
    /**
     * @param array $data
     * @return \PW\NewsletterBundle\Document\NewsletterEmail
     */
    public function create(array $data = array())
    {
        /* @var $page \PW\NewsletterBundle\Document\NewsletterEmail */
        $newsletterEmail = parent::create($data);
        return $newsletterEmail;
    }

}
