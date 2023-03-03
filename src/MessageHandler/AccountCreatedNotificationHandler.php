<?php

// 2 Créer le handler messenger correspondant à la notification de création de compte client. 
// Ce handler prendra en charge la construction de l'e-mail de notification et l'envoi de l'e-mail à l'aide du composant mailer.

// src/MessageHandler/AccountCreatedNotificationHandler.php

namespace App\MessageHandler;

use App\Message\AccountCreatedNotification;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Email;

class AccountCreatedNotificationHandler implements MessageHandlerInterface
{
    private $mailer;
    private $adminEmail; // adminEmail est défini dans le fichier services.yaml

    public function __construct(MailerInterface $mailer, string $adminEmail)
    {
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
    }

    public function __invoke(AccountCreatedNotification $message)
    {
        $email = (new Email())
            ->from($this->adminEmail)
            ->to($message->getRecipientEmail())
            ->subject($message->getSubject())
            ->html($message->getContent());

        $this->mailer->send($email);
    }
}
