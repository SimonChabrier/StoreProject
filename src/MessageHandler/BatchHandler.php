<?php

namespace App\MessageHandler;

use Twig\Environment;
use Symfony\Component\Mime\Email;
use App\Message\EmailNotification;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\BatchHandlerTrait;
use Symfony\Component\Messenger\Handler\Acknowledger;
use Symfony\Component\Messenger\Handler\BatchHandlerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class BatchHandler implements BatchHandlerInterface
{
    use BatchHandlerTrait;

    private $mailer;
    private $twig;
    private $jobs = [];
    private $ack = null;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function __invoke(EmailNotification $message, Acknowledger $ack = null) {
        return $this->handle($message, $ack);
    }

    public function handle($message)
    {
        // on ajoute le message à la liste des jobs en attente
        $this->jobs[] = [(new Email())
            ->from(new Address($message->getFrom(), 'Sneaker-Shop'))
            ->to(new Address($message->getTo()))
            ->subject($message->getSubject())
            ->html($this->twig->render($message->getTemplate(), $message->getContext()))
        ];

        // on vérifie si le nombre de jobs atteint le seuil pour effectuer le traitement par lot
        if ($this->shouldFlush()) {
            $this->process($this->jobs);
            $this->jobs = [];
        }
    }

    // Optionally, you can redefine the `shouldFlush()` method
    // of the trait to define your own batch size
    private function shouldFlush(): bool
    {
        return 10 <= \count($this->jobs);
    }

    private function process(array $jobs): void
    {
        foreach ($jobs as [$email]) {
            try {
                // Envoie du mail
                $this->mailer->send($email);
            } catch (\Throwable $e) {
                // traitement des erreurs
            }
        }
        $this->ack->ack();
    }
}
