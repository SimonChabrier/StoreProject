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

// A utiliser après supression de l'entité Picture pour supprimer les images du dossier uploads
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;

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
            BeforeEntityUpdatedEvent ::class => ['setPicture'],
            AfterEntityDeletedEvent::class => ['deletePicture']
        ];
    }

    /**
     * Pour mettre à jour les images d'un produit
     *
     * @param [type] $event
     * @return void
     */
    public function setPicture($event)
    {   

        if(!$this->request->getCurrentRequest()->files->get('Product')) {
            return;
        }
        // on récupère l'entité c'est à dire le produit
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Product)) {
            return;
        }

        // on enlève les images qui n'ont pas de nom de fichier parce que EasyAdmin ajoute directement
        // les images à l'objet à la soumission du formulaire même si elles ne sont pas traitées par le service d'upload.
        foreach ($entity->getPictures() as $picture) {
            if ($picture->getFileName() === null) {
                $entity->removePicture($picture);
            }
        }
        // dans un service ou listener on ne peut pas utiliser $this->request->files->get('Product')
        // on doit récupèrer les données de la requête avec getCurrentRequest() parce que la requête
        // est déjà passée quand on arrive dans le service.
        // On injecte donc RequestStack au lieu de Request dans le constructeur
        $data = $this->request->getCurrentRequest()->files->get('Product');

        // on boucle sur les images uploadées pour les traiter
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

                $this->em->persist($newPicture);
                // on donne à la picture de l'entité le nom du fichier uploadé
                $entity->addPicture($newPicture);
            }
        }

        $this->em->flush();

        $this->productRepository->add($entity, true);

    }

    /**
     * Pour créer les images d'un produit si le produit est nouveau
     *
     * @param [type] $event
     * @return void
     */
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

                $this->em->persist($newPicture);
                // on donne à la picture de l'entité le nom du fichier uploadé
                $entity->addPicture($newPicture);
            }
        }

        $this->em->flush();

        $this->productRepository->add($entity, true);

    }

    /**
     * Pour supprimer les images d'un produit
     *
     * @param [type] $event
     * @return void
     */
    public function deletePicture($event)
    { 
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Product)) {
            return;
        }

        foreach ($entity->getPictures() as $picture) {
            // dump($picture);
            // dump($entity->getPictures());
            // dump($entity->getPictures()->contains($picture));
            // dd($entity);
            $this->uploadService->deletePictures($picture);
        }

    }

}