<?php

Namespace App\EventSubscriber;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\ClearCacheService;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber as DoctrineEventSubscriber; 

class ClearCacheSubscriber implements DoctrineEventSubscriber
{
    private $clearCacheService;

    const CACHE_KEY = 'home_data';

    public function __construct(
        ClearCacheService $clearCacheService
    )
    {
        $this->clearCacheService = $clearCacheService;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
        ];
    }

    // met à jour le cache et le json après la création d'une entité
    public function postPersist(LifecycleEventArgs $args)
    {   
        $entity = $args->getObject();

        // Exclure les entités Order et OrderItem du cache
        if (!$entity instanceof Order && !$entity instanceof OrderItem) {
            // On supprime le cache et on refait le json
// todo pose problème si on utilise messenger il faudra utiliser le workflow ou le post persit pour créer le fichier json
$this->clearCacheService->clearCacheAndJsonFile(self::CACHE_KEY);
        }
    }

    // met à jour le cache après une modification d'entité
    public function postUpdate(LifecycleEventArgs $args)
    {   
        $entity = $args->getObject();

        // Exclure les entités Order et OrderItem du cache
        if (!$entity instanceof Order && !$entity instanceof OrderItem) {
            // On supprime le cache et on refait le json
// todo pose problème si on utilise messenger il faudra utiliser le workflow ou le post persit pour créer le fichier json
$this->clearCacheService->clearCacheAndJsonFile(self::CACHE_KEY);
        }
    }
}
