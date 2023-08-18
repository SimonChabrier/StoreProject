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
    ) {
        $this->uploadService = $uploadService;
        $this->request = $request;
        $this->em = $em;
        $this->productRepository = $productRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['setProductPictures'], // après création d'un produit
            BeforeEntityUpdatedEvent::class => ['setProductPictures'], // après modification d'un produit
            AfterEntityDeletedEvent::class => ['deletePicturesForDeletedProduct'] // après suppression d'un produit
        ];
    }

    /**
     * Pour créer et mettre à jour les images d'un produit
     *
     * @param [type] $event
     * @return void
     */
    public function setProductPictures($event)
    {
        //TODO faire en async avec messenger...

        // on récupère l'entité c'est à dire le produit
        $product = $event->getEntityInstance();
        if (!($product instanceof Product)) {
            return;
        }

        // et on nettoie les données soumises par le formulaire pour supprimer les images qui n'ont pas encore de nom de fichier
        // car si les données sont soummises parce que la requête est déjà passée quand on arrive dans le service,
        // elles ne sont pas encore traités par le service d'upload donc à ce moment là elles n'ont pas encore de nom de fichier.
        $this->cleanSubmitedFormPictures($product);

        // on récupère la requête courante pour avoir accès aux données du formulaire
        // dans un service ou listener on ne peut pas utiliser $this->request->files->get('Product')
        // on doit récupèrer les données de la requête avec getCurrentRequest() parce que la requête
        // est déjà passée quand on arrive dans le service.
        $current_request = $this->request->getCurrentRequest();
        $data = $current_request->get('Product')['pictures'];
        $files = $current_request->files->get('Product')['pictures'];
        
        if (isset($files)) {

            // on boucle sur les images uploadées pour les traiter
            foreach ($files as $i => $file) {
                if ($file['file'] !== null) {
                    // on destructe le tableau pour récupérer les données de chaque image
                    [$name, $alt, $file] = [$data[$i]['name'], $data[$i]['alt'], $file['file']];
                    // on crée un fichier temporaire pour pouvoir le traiter
                    $tempFileName = $this->uploadService->createTempFile($file);
                    $tempFile = $this->uploadService->getTempFile($tempFileName);
                    // on utilise le service d'upload pour traiter les images uploadées
                    $this->uploadService->uploadProductPictures($name, $alt, $tempFile, $product);
                }
            }
        }

        $this->em->flush();
        $this->productRepository->add($product, true);

        $this->deleteProductOrphansPictures();

    }

    /**
     * Pour supprimer les images d'un produit
     * lors de la suppression du produit
     * @param [type] $event
     * @return void
     */
    public function deletePicturesForDeletedProduct($event)
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
    public function deleteProductOrphansPictures()
    {
        $orphan_pictures = $this->em->getRepository(Picture::class)->findBy(['product' => null]);

        foreach ($orphan_pictures as $orphan_picture) {
            $this->em->remove($orphan_picture);
            $this->uploadService->deletePictures($orphan_picture);
        }
    }

    /**
     * nettoyer la soumission du formulaire
     *
     * @param Entity $product
     * @return void
     */
    public function cleanSubmitedFormPictures($product)
    {
        // on enlève les images qui n'ont pas de nom de fichier parce que EasyAdmin ajoute directement
        // les images à l'objet à la soumission du formulaire et elles ne sont pas encore traitées par le service d'upload.
        foreach ($product->getPictures() as $picture) {
            if ($picture->getFileName() === null) {
                $product->removePicture($picture);
            }
        }
    }
}
