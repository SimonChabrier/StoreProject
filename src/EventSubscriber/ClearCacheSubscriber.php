<?php

use Faker\Guesser\Name;
use PhpParser\Builder\Namespace_;

Namespace App\EventSubscriber;

use App\Entity\Order;
use Doctrine\ORM\Events;
use App\Entity\OrderItem;
use Doctrine\Common\EventSubscriber;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ClearCacheSubscriber implements EventSubscriber
{
    private $cache;

    public function __construct(AdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postUpdate,
            Events::postPersist,
        ];
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // Exclure les entités Order et OrderItem du cache
        if (!$entity instanceof Order && !$entity instanceof OrderItem) {
            $this->invalidateCache();
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // Exclure les entités Order et OrderItem du cache
        if (!$entity instanceof Order && !$entity instanceof OrderItem) {
            $this->invalidateCache();
        }
    }

    // Méthode pour invalider le cache
    private function invalidateCache()
    {
        $this->cache->clear();
    }
}
