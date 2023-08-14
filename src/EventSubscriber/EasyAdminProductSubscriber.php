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

    public function __construct(
        UploadService $uploadService, 
        RequestStack $request, 
        EntityManagerInterface $em, 
        ProductRepository $productRepository
        )
    {
        $this->uploadService = $uploadService;
        $this->request = $request;
        $this->em = $em;
        $this->productRepository = $productRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['setPicture'], // après création d'un produit
            BeforeEntityUpdatedEvent ::class => ['setPicture'], // après modification d'un produit
            AfterEntityDeletedEvent::class => ['deletePicture'] // après suppression d'un produit
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
        $product = $event->getEntityInstance();

        if (!($product instanceof Product)) {
            return;
        }

        // on enlève les images qui n'ont pas de nom de fichier parce que EasyAdmin ajoute directement
        // les images à l'objet à la soumission du formulaire même si elles ne sont pas traitées par le service d'upload.
        foreach ($product->getPictures() as $picture) {
            if ($picture->getFileName() === null) {
                $product->removePicture($picture);
            }
        }
        // dans un service ou listener on ne peut pas utiliser $this->request->files->get('Product')
        // on doit récupèrer les données de la requête avec getCurrentRequest() parce que la requête
        // est déjà passée quand on arrive dans le service.
        $data = $this->request->getCurrentRequest()->files->get('Product');
        $productPictures = $this->request->getCurrentRequest()->get('Product')['pictures'];

        // on boucle sur les images uploadées pour les traiter
        foreach ($data['pictures'] as $i => $pictureFile) {
           
            if ($pictureFile['file'] !== null) {

                [$name, $alt] = [$productPictures[$i]['name'], $productPictures[$i]['alt']];
                
                $newPicture = new Picture();
                $newPicture->setName($name);
                $newPicture->setAlt($alt);

                $picture = $this->uploadService->uploadPictures(
                    $pictureFile,
                    $newPicture,
                    $product
                );
                           
                $newPicture->setFileName($picture->getFileName());

                $this->em->persist($newPicture);
                // on donne à la picture de l'entité le nom du fichier uploadé
                $product->addPicture($newPicture);
            }
        }

        $this->em->flush();

        $this->productRepository->add($product, true);

        // on supprimer les images orphelines
        $this->deleteOrphansPictures();

    }

    /**
     * Pour supprimer les images d'un produit
     * lors de la suppression du produit
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
            $this->uploadService->deletePictures($picture);
        }

    }

    /**
     * Supprimer les images orphelines
     * qui ne sont pas liées à un produit 
     *
     * @return void
     */
    public function deleteOrphansPictures()
    {
        $orphan_pictures = $this->em->getRepository(Picture::class)->findBy(['product' => null]);
        foreach ($orphan_pictures as $orphan_picture) {
            $this->em->remove($orphan_picture);
            $this->uploadService->deletePictures($orphan_picture);
        }
    }
}