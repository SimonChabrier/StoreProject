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
            BeforeEntityPersistedEvent::class => ['setPicture'], // après création d'un produit
            BeforeEntityUpdatedEvent::class => ['setPicture'], // après modification d'un produit
            AfterEntityDeletedEvent::class => ['deletePicture'] // après suppression d'un produit
        ];
    }

    /**
     * Pour créer et mettre à jour les images d'un produit
     *
     * @param [type] $event
     * @return void
     */
    public function setPicture($event)
    {

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

        if (isset($current_request->files->get('Product')['pictures'])) {
            // sur la requête on récupère les fichiers uploadés
            $submited_files = $current_request->files->get('Product')['pictures'];
            // sur la requête on récupère les données soumises par le formulaire (alt et name)
            $submited_data = $current_request->get('Product')['pictures'];

            // on boucle sur les images uploadées pour les traiter
            foreach ($submited_files as $i => $submited_file) {

                if ($submited_file['file'] !== null) {

                    [$name, $alt] = [$submited_data[$i]['name'], $submited_data[$i]['alt']];

                    $newPicture = new Picture();
                    $newPicture->setName($name);
                    $newPicture->setAlt($alt);

                    $picture = $this->uploadService->uploadPictures(
                        $submited_file,
                        $newPicture,
                        $product
                    );

                    $newPicture->setFileName($picture->getFileName());

                    $this->em->persist($newPicture);
                    // on donne à la picture de l'entité le nom du fichier uploadé
                    $product->addPicture($newPicture);
                }
            }
        }

        $this->em->flush();
        $this->productRepository->add($product, true);

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

        $orphan_pictures_count = count($orphan_pictures);
        $orphan_pictures_deleted_count = 0;

        foreach ($orphan_pictures as $orphan_picture) {
            $this->em->remove($orphan_picture);
            $this->uploadService->deletePictures($orphan_picture);
            $orphan_pictures_deleted_count++;
        }

        if ($orphan_pictures_count === $orphan_pictures_deleted_count) {
            return true;
        } else {
            throw new \Exception('Erreur lors de la suppression des images orphelines');
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
