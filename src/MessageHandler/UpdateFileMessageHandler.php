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
        // // convertir le code du fichier en fichier
        // $decoded_file = base64_decode($message->getFileContent());
        // // faire un tableau et mettre le file content à la clé 'file'
        // $file = [
        //     'file' => $decoded_file
        // ];

        // Convertir le code du fichier en binaire
   $fileInfo = unserialize($message->getRealPAth());

    // Créer un objet UploadedFile simulé (c'est-à-dire un tableau associatif avec la clé 'file')
    $fileData = [
        'file' => new UploadedFile(
            $fileInfo['tmp_name'], // le chemin du fichier
            $fileInfo['name'], // le nom du fichier
            $fileInfo['type'], // le type du fichier
            $fileInfo['size'], // la taille du fichier
            false, // le code d'erreur fourni par le client
            false // si le fichier a été téléchargé via HTTP ou pas
        )
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




