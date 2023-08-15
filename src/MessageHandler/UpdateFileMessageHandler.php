<?php 

namespace App\MessageHandler;

use App\Service\UploadService;
use App\Message\UpdateFileMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

        $uniqueFileName = $this->uploadService->getOriginalFile($message->getName());

        $picture = $this->uploadService->processAndUploadPicture(
            $message->getName(),
            $message->getAlt(),
            $uniqueFileName,
            unserialize($message->getProduct())
        );

        // on récumère le produit avec son id pour pouvoir lui ajouter l'image
        $product = $this->entityManager->getRepository('App:Product')->find($message->getProductId());
        
        // on persiste l'image
        $this->entityManager->persist($picture);
        // on ajoute l'image au produit
        $product->addPicture($picture);
        // on persiste le produit
        $this->entityManager->persist($product);
        // on enregistre en base de données
        $this->entityManager->flush();
    }
}




