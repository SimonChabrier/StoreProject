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
        
        // Persist and flush the entity
        $this->entityManager->persist($picture);
        //$this->entityManager->flush();

        // on lui ajoute l'image
        $product->addPicture($picture);
        $this->entityManager->flush();
    }
}




