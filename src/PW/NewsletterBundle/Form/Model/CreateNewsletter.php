<?php

namespace PW\NewsletterBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert,
    PW\NewsletterBundle\Document\Newsletter;

class CreateNewsletter
{
    /**
     * @Assert\Type(type="PW\NewsletterBundle\Document\Newsletter")
     * @Assert\Valid
     */
    protected $newsletter;

    /**
     * @param Newsletter $newsletter
     */
    public function __construct(Newsletter $newsletter = null)
    {
        $this->newsletter = $newsletter;
    }

    /**
     * @param Newsletter $newsletter
     */
    public function setNewsletter(Newsletter $newsletter)
    {
        $this->newsletter = $newsletter;
    }

    /**
     * @return \PW\NewsletterBundle\Document\Newsletter
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }
}
