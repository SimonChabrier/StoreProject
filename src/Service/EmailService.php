<?php

namespace App\Service;

use App\Message\AdminNotification;
use App\Message\EmailNotification;
use App\Message\AccountCreatedNotification;
use Symfony\Component\Messenger\MessageBusInterface;

class EmailService
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function sendAccountCreatedNotification($email)
    {
        $message = new AccountCreatedNotification(
            $email,
            'Votre compte a été créé',
            'Bonjour ' . $email . ', votre compte a été créé avec succès.'
        );

        $this->bus->dispatch($message);
    }

    public function sendAdminNotification($subject, $email, $status)
    {
        $message = new AdminNotification($subject, $email, $status);

        $this->bus->dispatch($message);
    }

    public function sendEmailNotification($from, $to, $subject, $template, $context)
    {
        $message = new EmailNotification($from, $to, $subject, $template, $context);

        $this->bus->dispatch($message);
    }
}
