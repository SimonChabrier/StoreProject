<?php 

namespace App\EventSubscriber;

use App\Entity\Picture;
use App\Entity\Product;
use App\Service\UploadService;

use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EasyAdminProductSubscriber implements EventSubscriberInterface
{
    private $uploadService;
    private $request;

    public function __construct(UploadService $uploadService, RequestStack $request)
    {
        $this->uploadService = $uploadService;
        $this->request = $request;

    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['setPicture'],
            BeforeEntityUpdatedEvent::class => ['setPicture']
        ];
    }

    public function setPicture($event)
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Product)) {
            return;
        }
        
        // get uploaded files from the request
        $uploadedFiles = $this->request->getCurrentRequest()->files->get('Product')['pictures'];

        $product = $event->getEntityInstance();

        foreach ($uploadedFiles as $uploadedFile) {
            // Traitez le fichier uploadé comme vous le souhaitez (par exemple, passez-le à votre service d'upload)

            //TODO si la clé file est différente de null on traite l'upload sinon on ne fait rien
            if($uploadedFile['file'] !== null) {
                $this->uploadService->uploadPictures(
                    $uploadedFile, 
                    $product->getPictures()[0],
                    $product
                );
            }
        
        }
    }

}