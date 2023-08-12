<?php

namespace App\Service;

// On supprime le cache et on refait le json

use App\Repository\ProductRepository;
use App\Service\JsonManager;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class ClearCacheService {

    private $productRepository;
    private $cache;
    private $jsonManager;

    public function __construct(
        ProductRepository $productRepository, 
        AdapterInterface $cache, 
        JsonManager $jsonManager
        )
    {   
        $this->productRepository = $productRepository;
        $this->cache = $cache;
        $this->jsonManager = $jsonManager;
    }

    /**
     * Invalidate the cache
     * Create a new json file with the products data
     * @return void
     */
    public function clearCacheAndJsonFile($cacheKey): void
    {
        $this->jsonManager->jsonFileInit(
            $this->productRepository->findAll(), 
            'product:read', 
            'product.json', 
            'json'
        );
        
        $cacheItem = $this->cache->getItem($cacheKey);
        $isCacheHit = $cacheItem->isHit();

        $isCacheHit ? $this->cache->deleteItem($cacheKey) : null;

        
    }


}