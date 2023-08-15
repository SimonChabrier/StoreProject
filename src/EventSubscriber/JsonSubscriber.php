<?php

namespace App\EventSubscriber;

use App\Service\JsonManager;
use App\Repository\ProductRepository;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JsonSubscriber implements EventSubscriberInterface 
{
    private $jsonManager;
    private $productRepository;

    public function __construct(
        JsonManager $jsonManager, 
        ProductRepository $productRepository
        )
    {
        $this->jsonManager = $jsonManager;
        $this->productRepository = $productRepository;
    }

    /**
     * Create a json file with the users data if the index method is called and the json file doesn't exist
     * or if the json file exist and is older than 3600 seconds (1 hour) create a new json file with the products data
     * 
     * @param ControllerEvent $event
     * @return void
     */
    public function onKernelController(): void
    {   
        
        $fileName = 'product.json';
        $serializationFormat = 'json';
        $serializationGroups = 'product:read';
        // $fileCreationDate vaudra false si le fichier json n'existe pas ou le timestamp de création du fichier si le fichier json existe
        $fileCreationDate = $this->jsonManager->checkJsonFile($fileName);
        // nombre de secondes à évaluer entre la date de création du fichier et la date actuelle avant de créer un nouveau fichier json
        $time = 3600;
        
        // est ce que la page est en cache ou pas ?
        //$isCached = $this->cache->getItem('home_data')->get();
        // si elle est en cache et qu'on refait le json il faut vider le cache pour que la page soit à jour

        if(!$fileCreationDate) {
            $products = $this->productRepository->findAll();
$this->jsonManager->jsonFileInit($products, $serializationGroups, $fileName, $serializationFormat);
        } else {
            if(time() - $fileCreationDate > $time) {
                $products = $this->productRepository->findAll();
$this->jsonManager->jsonFileInit($products, $serializationGroups, $fileName, $serializationFormat);
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}