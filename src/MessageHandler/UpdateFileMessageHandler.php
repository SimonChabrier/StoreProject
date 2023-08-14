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
        // On déserialise le tableau associatif qui a été sérialisé dans le contrôleur
        // $fileInfo = unserialize($message->getRealPAth());

        // $uploadedFile = new UploadedFile(
        //     $fileInfo['tmp_name'],
        //     $fileInfo['name'],
        //     $fileInfo['type'],
        //     $fileInfo['size'],
        //     false,
        //     true // Changez ici pour true si le fichier a été téléchargé via HTTP
        // );

        // je reçoit des données brutes pour le fichier il faut les traiter avant de les envoyer à la méthode processAndUploadPicture
        $fileInfo = unserialize($message->getRealPath());
        // je recréer un objet UploadedFile à partir des données brutes
        $uploadedFile = new UploadedFile(
            $fileInfo['path'],
            $fileInfo['originalName'],
            $fileInfo['mimeType'],
            $fileInfo['error'],
            $fileInfo['test'],
        );


    // Créer un objet UploadedFile simulé (c'est-à-dire un tableau associatif avec la clé 'file')
    $fileData = [
        'file' => $$uploadedFile, 
    ];

        $picture = $this->uploadService->processAndUploadPicture(
            $message->getName(),
            $message->getAlt(),
            $fileData,
            unserialize($message->getProduct())
        );

        // Persist and flush the entity
        $this->entityManager->persist($picture);
        $this->entityManager->flush();
    }
}




