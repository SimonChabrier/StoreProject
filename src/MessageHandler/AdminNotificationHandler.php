<?php 

namespace App\MessageHandler;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\AdminNotification;

// 2 un handler qui envoie un email à l'admin avec le type d'événement déclenché 
// l'événement est récupéré dans le constructeur de la classe AdminNotification.php

class AdminNotificationHandler implements MessageHandlerInterface
{
    private $mailer;
    private $adminEmail;

    public function __construct(MailerInterface $mailer, string $adminEmail)
    {
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
    }

    public function __invoke(AdminNotification $message)
    {   
        // on envoie un email à l'admin avec le type d'événement déclenché
        // l'événement est récupéré dans le constructeur de la classe AdminNotification.php

        $eventType = $message->getEventType();
        $email = $this->adminEmail;
        $status = $message->getStatus();
        $messageBody = sprintf('Un nouvel événement de type %s a été déclenché. Email: %s Statut: %s', $eventType, $message->getEmail(), $status);

        $email = (new Email())
            ->from('notifications@sneakes-shop.com')
            ->to($email)
            ->subject(sprintf('Nouvel événement : %s', $eventType))
            ->html($messageBody);

        $this->mailer->send($email);
    }
}
