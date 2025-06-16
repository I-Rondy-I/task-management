<?php
namespace App\Service;

use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;

class CustomMailer implements MailerInterface
{
    public function sendConfirmationEmailMessage(UserInterface $user): void
    {
        // Реализация отправки email
    }

    public function sendResettingEmailMessage(UserInterface $user): void
    {
        // Реализация отправки email
    }
}