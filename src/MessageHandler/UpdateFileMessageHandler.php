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
        $tempFile = $this->uploadService->getTempFile($message->getTempFileName());
        // on récumère le produit avec son id pour pouvoir lui ajouter l'image
        //$product = $this->entityManager->find('App\Entity\Product', $message->getProductId());

        if(!$tempFile) {
            return;
        }

        $picture = $this->uploadService->processAndUploadPicture(
            $message->getName(),
            $message->getAlt(),
            $tempFile,
            unserialize($message->getProduct()),
        );

        if($picture) {
            // on supprime le fichier original
            //$this->uploadService->deleteTempFile($tempFile);
    
        }
    }
}




