<?php

Namespace App\EventSubscriber;

use App\Entity\Order;
use Doctrine\ORM\Events;
use App\Entity\OrderItem;
use App\Service\JsonManager;
use App\Repository\ProductRepository;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Doctrine\Common\EventSubscriber as DoctrineEventSubscriber; 

class ClearCacheSubscriber implements DoctrineEventSubscriber
{
    private $cache;
    private $jsonManager;
    private $productRepository;

    public function __construct(
        AdapterInterface $cache, 
        JsonManager $jsonManager, 
        ProductRepository $productRepository
        )
    {
        $this->cache = $cache;
        $this->jsonManager = $jsonManager;
        $this->productRepository = $productRepository;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate,
            Events::postPersist,
        ];
    }

    public function preUpdate(LifecycleEventArgs $args)
    {   

        $entity = $args->getObject();

        // Exclure les entités Order et OrderItem du cache
        if (!$entity instanceof Order && !$entity instanceof OrderItem) {
            
            $this->invalidateCache();

            // on supprime le fichier json pour le recréer avec les nouvelles données à jour
            $products = $this->productRepository->findAll();
            $jsonFileName = 'product.json';
            
            if($this->jsonManager->jsonFileDelete($jsonFileName)) {
                $this->jsonManager->jsonFileInit(
                    $products, 'product:read', 
                    $jsonFileName, 
                    'json'
                );
            }
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // Exclure les entités Order et OrderItem du cache
        if (!$entity instanceof Order && !$entity instanceof OrderItem) {
            
            $this->invalidateCache();

            // $jsonFileName = 'product.json';
            // $this->jsonManager->jsonFileDelete($jsonFileName);

           
        }
    }

    // Méthode pour invalider le cache
    private function invalidateCache()
    {   
        $this->cache->deleteItem('home_data');
    }
}
