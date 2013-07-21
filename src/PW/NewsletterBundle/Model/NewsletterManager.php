<?php

namespace PW\NewsletterBundle\Model;

use PW\ApplicationBundle\Model\AbstractManager,
    PW\NewsletterBundle\Document\Newsletter,
    PW\UserBundle\Document\User;

class NewsletterManager extends AbstractManager
{
    /**
     * @param array $data
     * @return \PW\NewsletterBundle\Document\Newsletter
     */
    public function create(array $data = array())
    {
        /* @var $page \PW\NewsletterBundle\Document\Newsletter */
        $newsletter = parent::create($data);
        $newsletter->setHeading('Here is your weekly roundup:');
        return $newsletter;
    }

    /**
     * @return mixed
     */
    public function findAllForSending()
    {
        return $this->getRepository()->findAllForSending()->getQuery()->execute();
    }

    /**
     * @return \PW\NewsletterBundle\Mailer\Mailer
     */
    public function getMailer()
    {
        return $this->container->get('pw_newsletter.mailer');
    }
}
