<?php

namespace App\Service;

// On supprime le cache et on refait le json
// utilisée après les evenements messenger et postFlush
// MessageProcessedSubscriber.php et ClearCacheSubscriber.php

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
        $this->renewJsonFile();

        if ($cacheKey !== null) {
            $cacheItem = $this->cache->getItem($cacheKey);
            $isCacheHit = $cacheItem->isHit();

            if ($isCacheHit) {
                $this->cache->deleteItem($cacheKey);
            }
        }
    }

    /**
     * Create a new json file with the products data
     * @return void
     */
    public function renewJsonFile(): void
    {
        $this->JsonFileUtils->createJsonFile(
            $this->productRepository->findAll(), 
            'product:read', 
            'product.json', 
            'json'
        );
    }


}