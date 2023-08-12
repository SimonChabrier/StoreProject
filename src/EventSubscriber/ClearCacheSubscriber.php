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
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {   
        //dd('ici');
        $entity = $args->getObject();

        // Exclure les entités Order et OrderItem du cache
        if (!$entity instanceof Order && !$entity instanceof OrderItem) {
            
            $this->invalidateCache();

            // on supprime le fichier json existant qui n'est plus à jour 
            // car on a modifié une entité
            $products = $this->productRepository->findAll();
            $jsonFileName = 'product.json';
            
            // et si jsonFileDelete renvoie true on recrée le fichier json avec les nouvelles données
            if($this->jsonManager->jsonFileDelete($jsonFileName)) {
                $this->jsonManager->jsonFileInit(
                    $products, 'product:read', 
                    $jsonFileName, 
                    'json'
                );
            }
        }
    }

  
    // Méthode pour invalider le cache
    private function invalidateCache()
    {   
        $this->cache->deleteItem('home_data');
    }
}
