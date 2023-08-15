<?php 

namespace App\MessageHandler;

use App\Entity\Product;
use App\Service\UploadService;
use App\Message\UpdateFileMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
// serializer interface
use Symfony\Component\Serializer\SerializerInterface;

class UpdateFileMessageHandler implements MessageHandlerInterface
{   
    private $uploadService;
    private $entityManager;
    private $serializer;

    public function __construct(UploadService $uploadService, EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {   
        $this->uploadService = $uploadService;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
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
        $productid = $message->getProductId();
        //$deserialzedProduct = $this->serializer->deserialize($product, Product::class, 'json');
        
        if(!$tempFile) {
            return;
        }

        $picture = $this->uploadService->processAndUploadPicture(
            $message->getName(),
            $message->getAlt(),
            $tempFile,
            $productid
        );

        if($picture) {
            // on supprime le fichier original
            //$this->uploadService->deleteTempFile($tempFile);
    
        }
    }
}




