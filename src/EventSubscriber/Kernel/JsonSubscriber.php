<?php

namespace App\EventSubscriber\Kernel;

use App\Service\File\JsonFileUtils;
use App\Repository\ProductRepository;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

// Cette class est chargée de créer un fichier json avec les données des produits si le fichier n'existe pas
// ou si le fichier existe et qu'il est plus vieux que 3600 secondes (1 heure)
// Elle est appelée à chaque fois qu'une page est chargée
// TODO le fichier json est maintenant remis à jour après le nettoyage du cache ou après la création d'un nouveau produit
// TODO il n'est plu snecessaire de le mettre à jour à chaque fois qu'une page est chargée car il doit : soit déjà être à jour soit ne pas exister.

class JsonSubscriber implements EventSubscriberInterface 
{
    private $JsonFileUtils;
    private $productRepository;

    public function __construct(
        JsonFileUtils $JsonFileUtils, 
        ProductRepository $productRepository
        )
    {
        $this->JsonFileUtils = $JsonFileUtils;
        $this->productRepository = $productRepository;
    }

    /**
     * Create a json file on any page load if the json file doesn't exist
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
        $fileCreationDate = $this->JsonFileUtils->checkJsonFile($fileName);
        // nombre de secondes à évaluer entre la date de création du fichier et la date actuelle avant de créer un nouveau fichier json
        $time = 3600;
        
        // est ce que la page est en cache ou pas ?
        //$isCached = $this->cache->getItem('home_data')->get();
        // si elle est en cache et qu'on refait le json il faut vider le cache pour que la page soit à jour

        if(!$fileCreationDate) {
            $products = $this->productRepository->findAll();
            // si le fichier n'existe pas on le crée
            $this->JsonFileUtils->createJsonFile($products, $serializationGroups, $fileName, $serializationFormat);
        } else {
            if(time() - $fileCreationDate > $time) {
                $products = $this->productRepository->findAll();
                $this->JsonFileUtils->createJsonFile($products, $serializationGroups, $fileName, $serializationFormat);
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