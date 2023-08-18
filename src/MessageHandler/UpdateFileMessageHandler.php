<?php 

namespace App\MessageHandler;

use App\Service\UploadService;
use App\Message\UpdateFileMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;


class UpdateFileMessageHandler implements MessageHandlerInterface
{   
    private $uploadService;


    public function __construct(UploadService $uploadService)
    {   
        $this->uploadService = $uploadService;

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
        $productid = $message->getProductId();
        
        if(!$tempFile) {
            return;
        }

        $this->uploadService->uploadProductPictures(
            $message->getName(),
            $message->getAlt(),
            $tempFile,
            $productid
        );
    }
}




