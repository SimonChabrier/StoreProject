<?php

namespace App\EventSubscriber;

use App\Service\JsonManager;
use App\Repository\ProductRepository;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class JsonSubscriber extends AbstractController implements EventSubscriberInterface 
{

    public function __construct(JsonManager $jsonManager, ProductRepository $productRepository)
    {
        $this->jsonManager = $jsonManager;
        $this->productRepository = $productRepository;
    }

    /**
     * Create a json file with the users data if the index method is called and the json file doesn't exist
     *
     * @param ControllerEvent $event
     * @return void
     */
    public function onKernelController(ControllerEvent $event): void
    {   
        // if the controller is HomeController and the méthod is index() and the json file doesn't exist        
        if($event->getRequest()->attributes->get('_controller') == "App\Controller\HomeController::index" && !file_exists($this->getParameter('kernel.project_dir').'/public/json/product.json')) {
            $products = $this->productRepository->findAll();            
            
            // create a json file with goups of data
            $this->jsonManager->jsonFileInit($products, 'product:read', 'product.json', 'json');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}