<?php

namespace App\MessageHandler;

use App\Message\EmailNotification;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class EmailNotificationHandler implements MessageHandlerInterface
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(EmailNotification $notification)
    {
       
        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($notification->getEmail())
            ->subject('Welcome!')
            ->text('Hello, your account has been created.');
        $this->mailer->send($email);
    }
}