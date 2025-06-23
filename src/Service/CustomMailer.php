<?php
namespace App\Service;

use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;

class CustomMailer implements MailerInterface
{
    /**
     * @return void
     */
    public function sendConfirmationEmailMessage(UserInterface $user): void
    {
    }

    /**
     * @return void
     */
    public function sendResettingEmailMessage(UserInterface $user): void
    {
    }
}