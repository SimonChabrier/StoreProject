<?php

namespace App\EventSubscriber\Kernel;

use App\Service\Utils\ConfigurationService;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


// ce subscriber est chargé de récupérer les paramètres de configuration en bdd à partir du service ConfigurationService

class ConfigurationSubscriber implements EventSubscriberInterface 
{
    private $configurationService;

    public function __construct(ConfigurationService $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    /**
     * Get configuration parameters from database
     * 
     * @param ControllerEvent $event
     * @return void
     */
    public function onKernelController(ControllerEvent $event): void
    {   
       $this->configurationService->getConfiguration();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}