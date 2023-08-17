<?php

namespace App\Service;

use App\Entity\Picture;
use App\Entity\Product;
use App\Service\ResizerService;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadService
{   
    // certaines propriétés sont ajoutés ici parce que comme elles sont bindées dans services.yaml
    // il faut les ajouter dans au moins un service pour générer d'erreur de Symfony.
    private $adminEmail;
    private $picDir;
    private $docDir;
    private $manager;
    private $workflows;
    private $resizerService;


    public function __construct(
            $adminEmail,
            string $picDir, 
            string $docDir, 
            ResizerService $resizerService,
            EntityManagerInterface $manager,
            Registry $workflows
        )
    {
        $this->adminEmail =     $adminEmail;
        $this->picDir =         $picDir;
        $this->docDir =         $docDir;
        $this->resizerService = $resizerService;
        $this->manager =        $manager;
        $this->workflows =      $workflows;
    }

    /**
     * Génère un nom de fichier unique
     * et replace l'extension par .webp
     *
     * @param UploadedFile $file
     * @return String
     */
    public function setUniqueName()
    {   
        // on génère un nom de fichier unique et on va l'utliser pour stocker toutes les images
        // on les récupérera à partir de leur même nom de fichier mais dans des dossiers différents
        // ce sera plus simple pour les afficher dans la vue e fonction du besoin.
        // on remplace l'extension par .webp
        return md5 (uniqid()).'.webp';
    }

    /**
     * Upload d'une image unique depuis le controller Home en Asynchrone avec Messenger
     * La string de l'image est transmise par le message UpdateFileMessageHandler
     * 
     * @param string $file
     * @param Entity $pictureObjet
     */
    public function uploadPicture(string $file, $pictureObjet): void
    {   
        // on génère un nom de fichier unique pour le fichier webp qui sera créé
        // on va l'utiliser pour le nom de chaque image et pour la propriété fileName de l'objet Picture.
        $fileName = self::setUniqueName();
        // UpdateFileMessageHandler transmet un string $file au format binary qui est converti en ressource GD
        $newGdRessource = imagecreatefromstring($file);
        // on crée un nouveau fichier webp à partir de la ressource.
        $imagewebp = imagewebp($newGdRessource, $this->picDir.'/'.$fileName, 80);
        // on crée un nouvel objet UploadedFile à partir de $newGdRessource pour la passer au service ResizerService dans le format attendu.
        $imagewebp = new UploadedFile($this->picDir.'/'.$fileName, $fileName, null, null, true);
        // on redimensionne l'image avec le service ResizerService pour les 4 tailles d'affichage et on les stocke dans des dossiers différents
        self::moveAll($imagewebp, $fileName, 80);
        // on crée un nouvel objet Picture avec les infos reçues du formulaire et envoyées via le message UpdateFileMessage apellé dans le controller
        $picture = new Picture();
               $picture->setName($pictureObjet->getName())
                    ->setAlt($pictureObjet->getAlt())
                    ->setFileName($fileName);
        // appliquer la transition du workflow et flusher
        self::updateWorkflowAndFlush($picture, 'process');
    }

    public function updateWorkflowAndFlush($picture, $transition)
    {   
        // on récupère le workflow de l'entité Picture
        $stateMachine = $this->workflows->get($picture, 'picture_publishing');
        // on applique la transition
        $stateMachine->apply($picture, $transition);
        // on persiste et on flush
        $this->manager->persist($picture);

        try {
            $this->manager->flush();            
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    //TODO
    public function processAndUploadPicture(string $name,string $alt, $filesData, $product): Picture
    {   
        // si messenger alors $product est un ID sinon c'est un objet Product mais findOneBy va le trouver dans les 2 cas
        // je sui sobligé de faire comme ça pour le moment en utilisant messenger
        $product = $this->manager->getRepository(Product::class)->findOneBy(['id' => $product]);
      
        if(!$product) {
            throw new \Exception('Le produit n\'existe pas');
        } else {

            foreach($filesData as $fileData) {
                // on génère un nom de fichier unique pour le fichier webp qui sera créé
                $fileName = $this->setUniqueName();
                $this->moveAll($fileData, $fileName, 80);
                $this->moveOriginalFile($fileData, $fileName);

                // Crée une nouvelle instance de Picture
                $picture = new Picture();
                $picture
                    ->setName($name)
                    ->setAlt($alt)
                    ->setFileName($fileName);
                // Associez l'entité Picture au produit
                $picture->setProduct($product);

                // Persistez et flushiez l'entité Picture
                $this->manager->persist($picture);
            }
            
            $this->manager->flush();
            return $picture;
        }
    }

    public function moveOriginalFile($fileData, $fileName): void
    {   
        $fileData->move($this->picDir, $fileName);
    }

    public function createTempFile($fileData)
    {
        $fileName = $this->setUniqueName();
        $fileData->move($this->picDir, $fileName);
        return $fileName;
    }

    public function getTempFile($fileName)
    {   
        $picture = $this->picDir.'/'.$fileName;
        // on recrée un objet UploadedFile à partir du fichier original pour le passer au service ResizerService dans le format attendu.
        if($picture){
            $picture = new UploadedFile($picture, $fileName, null, null, true);
            return ['file' => $picture];
        } else {
            // exception
            throw new \Exception('Le fichier n\'existe pas');
        }
    }

    public function deleteTempFile($fileName)
    {   
        // supprime le fichier original
        if($fileName){
            unlink($this->picDir.'/'.$fileName);
        } else {
            // exception
            throw new \Exception('Le fichier n\'existe pas');
        }
    }
    
    /**
     * Uploade d'un fichier unique
     *
     * @param [type] $file
     * @param Entity $fileObject
     * @return void
     */
    public function uploadFile($file, $fileObject)
    {   
        $fileName = md5(uniqid()).'.'.$file->guessExtension();
        $file->move($this->docDir, $fileName);
        return $fileObject->setFileName($fileName);
    }

    /**
     * Upload d'une collection de fichiers
     *
     * @param array $filesArray
     * @param Entity $fileObject
     * @param Entity $productObject
     * @return File
     */
    public function uploadFiles(array $filesArray, $fileObject, $productObject)
    {   
        foreach($filesArray as $file) {
            
            $fileName = md5(uniqid()).'.'.$file->guessExtension();
            $file->move($this->picDir, $fileName);
            // A chaque itération, on initialise les propriétés de l'objet File avec les infos du formulaire
            $fileObject
                        ->setProduct($productObject)
                        ->setName($fileObject->getName())
                        ->setInfo($fileObject->getInfo())
                        ->setFileName($fileName);
                        
        }

        // A chaque itération, on retourne l'objet File initialisé avec un nom de fichier unique pour le stocker en BDD 
        // utlisé ensuite pour construire l'affichage du fichier dans la vue.
        
        return $fileObject;
    }

    /**
     * Ajoute une image dans les 4 dossiers de stockage des images
     * 1 - 150x150
     * 1 - 250x250
     * 1 - 400x400
     * 1 - 1280x720
     * @param UploadedFile $file
     * @param String $fileName (nom du fichier unique généré qui set setfileName() de l'objet Picture pour le stocker en BDD et l'utiliser pour construire l'affichage dans la vue)
     * @return void
     */
    public function moveAll($fileData, $fileName)
    {   
        // on redimensionne l'image et stcoke en local avec le service ResizerService
        $this->resizerService->cropAndMoveAllPictures($fileData, $fileName, 80);
        $this->resizerService->slider1280($fileData, $fileName, 50);
    }

    // TODO a améliorer sur la gestion des repertoires
    public function deletePictures($picture)
    {   
        // on récupère le nom du fichier à supprimer
        $fileName = $picture->getFileName();
       
        $allPictures = [
            // glob va chercher tous les fichiers dans les dossiers spécifiés et les stocker dans un tableau
            glob('../public/uploads/files/pictures/*'),
            glob('../public/uploads/files/pictures_XS/*'),
            glob('../public/uploads/files/pictures_250/*'),
            glob('../public/uploads/files/pictures_400/*'),
            glob('../public/uploads/files/pictures_1200/*'),
            glob('../public/uploads/files/slider_1280/*'),
        ];

        // Parcourir chaque tableau de fichiers dans $allPictures
        foreach ($allPictures as $folderFiles) {
            // Parcourir chaque fichier dans le tableau courant
            foreach ($folderFiles as $file) {
                // Obtenir les informations sur le fichier (nom, extension, etc.)
                $fileInfo = pathinfo($file);
                // Comparer le nom du fichier extrait avec $fileName
                if ($fileInfo['basename'] === $fileName) {
                    // Si les noms de fichiers correspondent, supprimer le fichier
                    unlink($file);
                }
            }
        };
    }

    

}