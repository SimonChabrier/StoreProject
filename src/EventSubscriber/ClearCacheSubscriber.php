<?php

Namespace App\EventSubscriber;

use App\Service\JsonManager;
use App\Entity\Order;
use Doctrine\ORM\Events;
use App\Entity\OrderItem;
use Doctrine\Common\EventSubscriber;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\Repository\ProductRepository;

class ClearCacheSubscriber implements EventSubscriber
{
    private $cache;
    private $jsonManager;
    private $productRepository;

    public function __construct(AdapterInterface $cache, JsonManager $jsonManager, ProductRepository $productRepository)
    {
        $this->cache = $cache;
        $this->jsonManager = $jsonManager;
        $this->productRepository = $productRepository;
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
            // on recrée le fichier json des produits
            $products = $this->productRepository->findAll();
            $this->jsonManager->jsonFileInit($products, 'product:read', 'product.json', 'json');
        }
    }

    // Méthode pour invalider le cache
    private function invalidateCache()
    {
        $this->cache->clear();
    }
}
