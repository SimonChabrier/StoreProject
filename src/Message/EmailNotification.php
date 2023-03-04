<?php

namespace App\Message;

class EmailNotification
{   
    // from config/services.yaml
    private $adminMail;

    private $from;
    private $to;
    private $subject;
    private $template = 'email/base_email.html.twig';
    private $context = [];

    public function __construct(string $from, string $to, string $subject = 'Notification', string $template = null, array $context = [], string $header = null, string $footer = null)
    {   
        $this->from = $from ?: $this->adminMail;
        $this->to = $to;
        $this->subject = $subject ?: 'Notification';
        $this->template = $template ?: 'email/base_email.html.twig';
        $this->context = $context ?: [];
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setTemplate(string $template = null): void
    {
        $this->template = $template ?: 'email/base_email.html.twig';
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setContext(array $context = []): void
    {
        $this->context = $context ?: [];
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
