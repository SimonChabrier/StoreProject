<?php

namespace App\EventSubscriber\EasyAdmin;

use App\Entity\Picture;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\File\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\UpdateFileMessage;

// A utiliser après supression de l'entité Picture pour supprimer les images du dossier uploads
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;

class EasyAdminProductSubscriber implements EventSubscriberInterface
{
    private $uploadService;
    private $request;
    private $em;
    private $productRepository;
    private $bus;


    const USE_MESSAGE_BUS = true;

    public function __construct(
        UploadService $uploadService,
        RequestStack $request,
        EntityManagerInterface $em,
        ProductRepository $productRepository,
        MessageBusInterface $bus

    ) {
        $this->uploadService = $uploadService;
        $this->request = $request;
        $this->em = $em;
        $this->productRepository = $productRepository;
        $this->bus = $bus;
    }

    /**
     * Pour créer et mettre à jour les images d'un produit
     *
     * @return array
     */
    public static function getSubscribedEvents() : array
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
        $product = $event->getEntityInstance();
        if (!($product instanceof Product)) {
            return;
        }

        $this->cleanSubmitedFormPictures($product);

        // Récupérer les données du formulaire imbriqué
        $current_request = $this->request->getCurrentRequest();
        $productData = $current_request->get('Product');
        // si l'utilisateur n'a pas ajouté de nouvelles images, on ne fait rien.
        // il n'a pas affiché le formulaire imbriqué dans le DOM donc le form imbriqué ['pictures'] n'existe pas dans la requête.
        // le reste du produit sera quand même mis à jour (nom, prix, etc.)
        if (!array_key_exists('pictures', $productData)) {
            return;
        }

        $data = $current_request->get('Product')['pictures']; // name et alt
        $files = $current_request->files->get('Product')['pictures']; // file

        if (!empty($files)) {
            // Filtrer les nouveaux fichiers pour ne traiter que les images non-null (les images déjà existantes sont null car by_reference = false)
            $newFiles = array_filter($files, function ($file) {
                return $file['file'] !== null;
            });

            foreach ($newFiles as $i => $file) {
                [$name, $alt, $file] = [$data[$i]['name'], $data[$i]['alt'], $file['file']];

                if (!self::USE_MESSAGE_BUS) {
                    $newFileName = $this->uploadService->saveOriginalPictureFile(file_get_contents($file), pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));

                    $this->uploadService->createProductPicture(
                        $name,
                        $alt,
                        $newFileName,
                        $product,
                    );
                } else {

                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $binaryContent = file_get_contents($file);

                    $this->bus->dispatch(
                        new UpdateFileMessage(
                            $name, // la valeur saisi dans le champ name du formulaire imbriqué
                            $alt, // la valeur saisi dans le champ alt du formulaire imbriqué
                            $product->getId(), // l'id du produit
                            $originalName, // le nom du fichier sans l'extension 
                            $binaryContent // le contenu du fichier au format binaire (c'est une string)
                        )
                    );
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
            $this->uploadService->deletePicture($picture);
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
            $this->uploadService->deletePicture($orphan_picture);
        }
    }

    /**
     * Supprime les images qui n'ont pas de nom de fichier parce que EasyAdmin ajoute directement depuis la requête
     * mais elles ne sont pas encore traitées par le service d'upload et donc pas encore liées à l'objet Product dans la base de données.
     * 
     * @param Entity $product
     * @return void
     */
    public function cleanSubmitedFormPictures($product)
    {
        foreach ($product->getPictures() as $picture) {
            if ($picture->getFileName() === null) {
                $product->removePicture($picture);
            }
        }
    }
}
