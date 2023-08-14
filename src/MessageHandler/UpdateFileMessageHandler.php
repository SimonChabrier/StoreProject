<?php 

namespace App\MessageHandler;

use App\Message\UpdateFileMessage;
use App\Service\UploadService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateFileMessageHandler implements MessageHandlerInterface
{   

    // non utilisé actuellement peut être utilisé sur la config d'upload de fichier unique
    // TODO adapter à l'upload de fichier multiple...
    
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
        return $this->uploadService->processAndUploadPicture(
            unserialize($message->getPictureObject()->getName()),
            unserialize($message->getPictureObject()->getAlt()),
            $message->getFile(), 
            unserialize($message->getPictureObject())
        );
    }

}



