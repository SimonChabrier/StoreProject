<?php 

// 1 Créer le message messenger correspondant à la notification de création de compte client. 
// Ce message contiendra les informations nécessaires pour envoyer l'e-mail de notification, 
// telles que l'adresse e-mail du destinataire, le sujet de l'e-mail et le contenu de l'e-mail.

namespace App\Message;

class AccountCreatedNotification
{
    private $recipientEmail;
    private $subject;
    private $content;

    public function __construct(string $recipientEmail, string $subject, string $content)
    {
        $this->recipientEmail = $recipientEmail;
        $this->subject = $subject;
        $this->content = $content;
    }

    public function getRecipientEmail(): string
    {
        return $this->recipientEmail;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
