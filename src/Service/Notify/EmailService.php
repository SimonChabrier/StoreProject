<?php

namespace App\Service\Notify;

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

    public function sendAdminNotification($subject, $email, $status)
    {
        $message = new AdminNotification($subject, $email, $status);
        return $this->bus->dispatch($message);
    }

    public function sendTemplateEmailNotification($from, $to, $subject, $template, $context)
    {   
        $message = new EmailNotification($from, $to, $subject, $template, $context);
        return $this->bus->dispatch($message);
    }

}