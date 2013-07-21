<?php

namespace PW\ApplicationBundle\Mailer;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Mailer
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var \UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var type
     */
    protected $userManager;

    /**
     * @param \Swift_Mailer $mailer
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
     * @param \Twig_Environment $twig
     */
    public function __construct(\Swift_Mailer $mailer, UrlGeneratorInterface $router, \Twig_Environment $twig)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig   = $twig;
    }

    /**
     * @param string $templateName
     * @param array $context
     * @param mixed $fromEmail
     * @param mixed $toEmail
     * @param string $category Category for sendgrid to track
     * @return int
     */
    public function sendMessage($templateName, $context, $fromEmail, $toEmail, $category = null)
    {
        // Load email template
        $template = $this->twig->loadTemplate($templateName);

        // Setup email variables
        $subject  = $template->renderBlock('subject', $context);
        $htmlBody = $template->renderBlock('body_html', array_merge($context, array('subject' => $subject)));
        $textBody = $template->renderBlock('body_text', array_merge($context, array('subject' => $subject)));

        // Create message
        $message = \Swift_Message::newInstance()->setSubject($subject);
        if (is_array($fromEmail)) {
            $message->setFrom($fromEmail['address'], $fromEmail['sender_name']);
        } else {
            $message->setFrom($fromEmail);
        }

        if (is_array($toEmail)) {
            foreach ($toEmail as $address => $name) {
                $message->addTo($address, $name);
            }
        } else {
            $message->setTo($toEmail);
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
        if ($category !== null) {
            $message->getHeaders()->addTextHeader('X-SMTPAPI', '{"category": "'. $category .'"}');    
        }
        

        return $this->mailer->send($message);
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters = array())
    {
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param type $userManager
     */
    public function setUserManager($userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @return \Swift_Mailer
     */
    public function getSwiftMailer()
    {
        return $this->mailer;
    }

    /**
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
     */
    public function setRouter(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @return \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    public function getRouter()
    {
        return $this->router;
    }
}