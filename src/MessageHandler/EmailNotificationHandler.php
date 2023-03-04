<?php

namespace App\MessageHandler;

use App\Message\EmailNotification;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailNotificationHandler implements MessageHandlerInterface
{
    private $mailer;
    private $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function __invoke(EmailNotification $message)
    {   
        // on récupère le template twig et les variables à passer à la vue twig pour générer le contenu du template du mail
        $body = $this->twig->render($message->getTemplate(), $message->getContext());
        
        $email = (new Email())
            ->from(new Address($message->getFrom(), 'Sneaker-Shop'))
            ->to(new Address($message->getTo()))
            ->subject($message->getSubject())
            ->html($body);

        //$this->mailer->send($email);
    }
}