<?php 

namespace App\EventSubscriber;

use App\Entity\Picture;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\UploadService;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;

class EasyAdminProductSubscriber implements EventSubscriberInterface
{
    private $uploadService;
    private $request;
    private $em;
    private $productRepository;

    public function __construct(UploadService $uploadService, RequestStack $request, EntityManagerInterface $em, ProductRepository $productRepository)
    {
        $this->uploadService = $uploadService;
        $this->request = $request;
        $this->em = $em;
        $this->productRepository = $productRepository;

    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['createPicture'],
            BeforeEntityUpdatedEvent ::class => ['setPicture']
        ];
    }

    public function setPicture($event)
    {   

        //TODO ne rien faire si l'action n'est pas l'upload d'une image
        if(!$this->request->getCurrentRequest()->files->get('Product')) {
            return;
        }

        $entity = $event->getEntityInstance();

        if (!($entity instanceof Product)) {
            return;
        }

        // on enlève les images qui n'ont pas de nom de fichier parce que EasyAdmin ajoute directement
        // les images dans le tableau de l'entité même si elles ne sont pas uploadées
        foreach ($entity->getPictures() as $picture) {
            if ($picture->getFileName() === null) {
                $entity->removePicture($picture);
            }
        }
    
        $data = $this->request->getCurrentRequest()->files->get('Product');

        foreach ($data['pictures'] as $i => $picture) {
           
            if ($picture['file'] !== null) {
                
                $newPicture = new Picture();
                $newPicture->setName($this->request->getCurrentRequest()->get('Product')['pictures'][$i]['name']);
                $newPicture->setAlt($this->request->getCurrentRequest()->get('Product')['pictures'][$i]['alt']);

                $picture = $this->uploadService->uploadPictures(
                    $picture,
                    $newPicture,
                    $entity
                );
                
                
                $newPicture->setFileName($picture->getFileName());

                dump($newPicture);

                $this->em->persist($newPicture);
                // on donne à la picture de l'entité le nom du fichier uploadé
                $entity->addPicture($newPicture);
            }
        }

        $this->em->flush();

        $this->productRepository->add($entity, true);

    }

    public function createPicture($event)
    {   

        $entity = $event->getEntityInstance();

        if (!($entity instanceof Product)) {
            return;
        }

        // on enlève les images qui n'ont pas de nom de fichier parce que EasyAdmin ajoute directement
        // les images dans le tableau de l'entité même si elles ne sont pas uploadées
        foreach ($entity->getPictures() as $picture) {
            if ($picture->getFileName() === null) {
                $entity->removePicture($picture);
            }
        }
    
        $data = $this->request->getCurrentRequest()->files->get('Product');

        foreach ($data['pictures'] as $i => $picture) {
           
            if ($picture['file'] !== null) {
                
                $newPicture = new Picture();
                $newPicture->setName($this->request->getCurrentRequest()->get('Product')['pictures'][$i]['name']);
                $newPicture->setAlt($this->request->getCurrentRequest()->get('Product')['pictures'][$i]['alt']);

                $picture = $this->uploadService->uploadPictures(
                    $picture,
                    $newPicture,
                    $entity
                );
                
                
                $newPicture->setFileName($picture->getFileName());

                dump($newPicture);

                $this->em->persist($newPicture);
                // on donne à la picture de l'entité le nom du fichier uploadé
                $entity->addPicture($newPicture);
            }
        }

        $this->em->flush();

        $this->productRepository->add($entity, true);

    }

}