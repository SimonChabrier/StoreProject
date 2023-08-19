<?php

namespace App\EventSubscriber;

use App\Message\UpdateFileMessage; // Remplacez par le nom de votre classe de message
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Service\ClearCacheService;

class MessageProcessedSubscriber implements EventSubscriberInterface
{
    private $messageBus;
    private $clearCacheService; 

    const CACHE_KEY = 'home_data';

    public function __construct(MessageBusInterface $messageBus, ClearCacheService $clearCacheService)
    {
        $this->messageBus = $messageBus;
        $this->clearCacheService = $clearCacheService;
    }

    public static function getSubscribedEvents()
    {
        return [
            WorkerMessageHandledEvent::class => 'onMessageHandled',
        ];
    }

    public function onMessageHandled(WorkerMessageHandledEvent $event)
    {
        $envelope = $event->getEnvelope();
        $message = $envelope->getMessage();

        if ($message instanceof UpdateFileMessage) {
            // On vider le cache et on met à jour le fichier JSON
            // après que le message ait été traité par le worker 
            // pour attendre que le worker ait fini de traiter le message
            // et que toutes les entités aient été mises à jour en base de données.
            $this->clearCacheService->clearCacheAndJsonFile(self::CACHE_KEY);
            
        }
    }
}
