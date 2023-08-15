<?php 

namespace App\MessageHandler;

use App\Service\UploadService;
use App\Message\UpdateFileMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateFileMessageHandler implements MessageHandlerInterface
{   
    private $uploadService;
    private $entityManager;

    public function __construct(UploadService $uploadService, EntityManagerInterface $entityManager)
    {   
        $this->uploadService = $uploadService;
        $this->entityManager = $entityManager;
    }

    public function __invoke(UpdateFileMessage $message)
    {   
        $this->updateFile($message);
    }

    public function updateFile($message)
    {   
        // On récupère le fichier dans le repertoire des fichiers originaux en cherchant avec son nom unique
        $originalFile = $this->uploadService->getOriginalFile($message->getName());
        // on récumère le produit avec son id pour pouvoir lui ajouter l'image
        $product = $this->entityManager->find('App\Entity\Product', $message->getProductId());

        $picture = $this->uploadService->processAndUploadPicture(
            $message->getName(),
            $message->getAlt(),
            $originalFile,
            $product
        );

        // TODO ici soucis avec le product.json qui n'est pas mis à jour ou qui n'existe pas encore et provoque une erreur
        
        // on persiste l'image
        $this->entityManager->persist($picture);
        // on ajoute l'image au produit
        $product->addPicture($picture);
        // on persiste le produit
        $this->entityManager->persist($product);
        // on enregistre en base de données
        $this->entityManager->flush();
        // on supprime le fichier original du dossier uploads
        $this->uploadService->deleteOriginalFile($originalFile);
    }
}




