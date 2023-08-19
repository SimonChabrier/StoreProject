<?php

Namespace App\EventSubscriber;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\ClearCacheService;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber as DoctrineEventSubscriber;
use Doctrine\ORM\Event\PostFlushEventArgs as PostFlushEventArgs;
use Symfony\Component\Workflow\Event\Event;

// Cette classe est chargée de supprimer le cache et de refaire le fichier json après chaque flush d'une entité
// Elle est appelée à chaque fois qu'une entité est créée, modifiée ou supprimée

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
            Events::postFlush,
            //Events::postPersist,
            //Events::postUpdate,
            // todo il faudra gèrer postRemove pour supprimer le fichier json
        ];
    }

    /**
     * met à jour le cache et le json après chaque flush d'une entité
     * exlue les entités Order et OrderItem.
     * @param PostFlushEventArgs $args
     * @return void
     */
    public function postFlush(PostFlushEventArgs $args)
    {   
        // on récupère les entités créées
        $entities = $args->getEntityManager()->getUnitOfWork()->getScheduledEntityInsertions();
        // on boucle sur les entités créées
        foreach ($entities as $entity) {
            // on exclut les entités Order et OrderItem
            if (!$entity instanceof Order && !$entity instanceof OrderItem) {
                // on supprime le cache et on refait le json
                $this->clearCacheService->clearCacheAndJsonFile(self::CACHE_KEY);
            }
        }

    }

    //*  NON UTILISE CAR ON UTLISE POST FLUSH
    // met à jour le cache et le json après la création d'une entité
    public function postPersist(LifecycleEventArgs $args)
    {   
        $entity = $args->getObject();

        // Exclure les entités Order et OrderItem du cache
        if (!$entity instanceof Order && !$entity instanceof OrderItem) {
            // On supprime le cache et on refait le json
            $this->clearCacheService->clearCacheAndJsonFile(self::CACHE_KEY);
        }
    }

    //*  NON UTILISE CAR ON UTLISE POST FLUSH
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
