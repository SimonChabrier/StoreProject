<?php

namespace App\Service;

// On supprime le cache et on refait le json

use App\Repository\ProductRepository;
use App\Service\JsonFileUtils;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class ClearCacheService {

    private $productRepository;
    private $cache;
    private $JsonFileUtils;

    public function __construct(
        ProductRepository $productRepository, 
        AdapterInterface $cache, 
        JsonFileUtils $JsonFileUtils
        )
    {   
        $this->productRepository = $productRepository;
        $this->cache = $cache;
        $this->JsonFileUtils = $JsonFileUtils;
    }

    /**
     * Invalidate the cache
     * Create a new json file with the products data
     * @return void
     */
    public function clearCacheAndJsonFile($cacheKey = null): void
    {   
        $this->JsonFileUtils->jsonFileInit(
            $this->productRepository->findAll(), 
            'product:read', 
            'product.json', 
            'json'
        );

        if ($cacheKey !== null) {
            $cacheItem = $this->cache->getItem($cacheKey);
            $isCacheHit = $cacheItem->isHit();

            if ($isCacheHit) {
                $this->cache->deleteItem($cacheKey);
            }
        }
    }


}