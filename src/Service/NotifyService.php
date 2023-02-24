<?php

namespace App\Service;

// créer une classe pour initialiser la consommation des notification de Messenger

use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;

class NotifyService
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function notify($message, $delay = 0)
    {
        $envelope = $this->bus->dispatch($message);
        if ($delay > 0) {
            $envelope = $envelope->with(new DelayStamp($delay));
        }
        $this->bus->dispatch($envelope);
    }
}

// utilisation de la classe NotifyService dans un controller 

// Instancier la classe NotifyService en lui passant une instance de MessageBusInterface
// $notifyService = new NotifyService($messageBus);

// Créer un objet qui représente le message à envoyer à la file d'attente
// $message = new MyMessage('Hello world');

// Appeler la méthode notify pour envoyer le message à la file d'attente
// $notifyService->notify($message, 30); // Le message sera envoyé dans 30 secondes