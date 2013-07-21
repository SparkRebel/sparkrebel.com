<?php

namespace PW\NewsletterBundle\Mailer;

use PW\ApplicationBundle\Mailer\Mailer as BaseMailer;
use PW\UserBundle\Document\User;
use PW\NewsletterBundle\Document\Newsletter;
use PW\NewsletterBundle\Document\NewsletterEmail;

class Mailer extends BaseMailer
{
    /**
     * @var \PW\NewsletterBundle\Model\NewsletterManager
     */
    protected $newsletterManager;

    /**
     * Sends the email to spool
     */
    public function sendEmail($subject, $fromEmail, $toEmail, $htmlBody, $textBody)
    {
        // Create message
        $message = \Swift_Message::newInstance()->setSubject($subject);
        if (is_array($fromEmail)) {
            $message->setFrom($fromEmail['address'], $fromEmail['sender_name']);
        } else {
            $message->setFrom($fromEmail);
        }

        if($this->getNewsletterManager()->getContainer()->getParameter('pw_newsletter.prod.active')) {
            if (is_array($toEmail)) {
                foreach ($toEmail as $address => $name) {
                    $message->addTo($address, $name);
                }
            } else {
                $message->setTo($toEmail);
            }
        }
        else {
            $message->setTo($this->getNewsletterManager()->getContainer()->getParameter('pw_newsletter.dev.email'));
        }

        if (rand(1,500) === 1) {
            $message->addBcc($this->getNewsletterManager()->getContainer()->getParameter('pw_newsletter.dev.email'));
        }

        if (empty($htmlBody)) {
            // text only
            $message->setBody($textBody);
        } else {
            if (empty($textBody)) {
                // html only
                $message->setBody($htmlBody, 'text/html');
            } else {
                // html+text
                $message->setBody($htmlBody, 'text/html')->addPart($textBody, 'text/plain');
            }
        }

        /**
         * adding specific tracking header for sendgrid
         *
         * X-SMTPAPI: {"category": "Category Name"}
         */
        $message->getHeaders()->addTextHeader('X-SMTPAPI', '{"category": "weekly"}');

        return $this->mailer->send($message);
    }

    /**
     * Send a newsletter to a user
     *
     * @param \PW\NewsletterBundle\Document\Newsletter $newsletter
     * @param \PW\UserBundle\Document\User $user
     * @param bool $justPreview
     * @return boolean
     */
    public function send(Newsletter $newsletter, User $user, $justPreview = false)
    {
        $this->parameters['newsletter'] = $newsletter;
        $this->parameters['user'] = $user;
        $this->parameters['to_email'] = array($user->getEmail() => $user->getName());

        $log = new NewsletterEmail();
        $log->setNewsletter($newsletter);
        $log->setUser($user);
        $log->setCode(str_replace('.','-',uniqid('', TRUE)));
        $log->setIsActive(true);

        // Load email template
        $template = $this->twig->loadTemplate($this->parameters['template']);

        // Setup email variables
        $subject  = $template->renderBlock('subject', $this->parameters);
        $this->parameters['subject'] = $subject;

        if(!$justPreview) {
            $this->parameters['log'] = $log;
            $this->parameters['viewAsWebpage'] = true;

            $htmlBody = $template->renderBlock('body_html', $this->parameters);
            $textBody = $template->renderBlock('body_text', $this->parameters);
        }

        $this->parameters['viewAsWebpage'] = false;
        $this->parameters['log'] = null;

        $htmlBodyForLog = $template->renderBlock('body_html', $this->parameters);
        $textBodyForLog = $template->renderBlock('body_text', $this->parameters);

        $log->setContent($htmlBodyForLog);
        $this->getNewsletterManager()->getDocumentManager()->persist($log);
        $this->getNewsletterManager()->getDocumentManager()->flush($log);

        if(!$justPreview) {
            return $this->sendEmail($subject, $this->parameters['from_email'], $this->parameters['to_email'], $htmlBody, $textBody);
        }
        else {
            return $log;
        }
    }

    /**
     * @param \PW\NewsletterBundle\Model\NewsletterManager $newsletterManager
     */
    public function setNewsletterManager($newsletterManager)
    {
        $this->newsletterManager = $newsletterManager;
    }

    /**
     * @return \PW\NewsletterBundle\Model\NewsletterManager
     */
    public function getNewsletterManager()
    {
        return $this->newsletterManager;
    }
}