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
        $header = $this->twig->render($message->getHeader(), $message->getContext());
        $body = $this->twig->render($message->getTemplate(), $message->getContext());
        $footer = $this->twig->render($message->getFooter(), $message->getContext());
        
        $email = (new Email())
            ->from(new Address($message->getFrom(), 'Sneaker-Shop'))
            ->to(new Address($message->getTo()))
            ->subject($message->getSubject())
            ->html($header . $body . $footer);

        $this->mailer->send($email);
    }
}
