<?php

namespace PW\UserBundle\Tests\Mailer;

use PW\ApplicationBundle\Tests\WebTestCase;

/**
 * @group email
 */
class MailerTest extends WebTestCase
{
    /**
     * @covers \PW\UserBundle\Mailer\Mailer::sendWelcomeEmailMessage
     */
    public function testSendWelcomeEmail()
    {
        $this->loadFixtures(array('PW\UserBundle\DataFixtures\MongoDB\TestUsers'));

        $userManager = $this->getContainer()->get('pw_user.user_manager');
        $mailer = $userManager->getMailer();

        $transport = $this->getContainer()->get('swiftmailer.transport.real');
        if ($transport instanceOf \Swift_Transport_NullTransport) {
            $this->markTestSkipped('Swift_Mailer delivery is disabled.');
        }

        $user = $userManager->getRepository()->findOneByName("User #1");
        $mailer->sendWelcomeEmailMessage($user);

        $result = $this->runCommand('swiftmailer:spool:send', array('--message-limit' => 1));
        $this->assertContains('sent 1 emails', $result);
    }
}