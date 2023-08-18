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
        $this->createFile($message);
    }

    public function createFile($message)
    {   
        $tempFileName = $this->uploadService->saveOriginalFile(
            $message->getBinaryContent(),
            $message->getOriginalName(),
        );

        if(!$tempFileName){
            // runtime exception simple de PHP parce que c'est une erreur qui ne peut pas être anticipée
            // si le fichier n'est pas créé, on ne peut pas continuer le processus...
            new \RuntimeException('Erreur lors de la création du fichier');
        }

        $this->updateFile($message, $tempFileName);
    }

    public function updateFile($message, $tempFileName)
    {   
        $this->uploadService->createProductPicture(
            $message->getName(),
            $message->getAlt(),
            $tempFileName,
            $message->getProductId(),
        );
    }


}




