<?php

namespace App\Message;

// 1 une classe de notification générale pour les notifications de l'admin
class AdminNotification
{
    private $eventType;
    private $email;
    private $status;

    public function __construct(string $eventType, string $email, string $status)
    {
        $this->eventType = $eventType;
        $this->email = $email;
        $this->status = $status;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getStatus(): string
    {
        return $this->status;
    }


}
