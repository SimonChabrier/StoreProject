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
    public function setUniqueName() : string
    {   
        // on utlise un hashage md5 simple pour générer un nom de fichier unique
        return md5 (uniqid()) . '.webp';
    }

    /**
     * met à jour le workflow de l'entité Picture
     * et flush
     * TODO : à améliorer voir si on peut pas faire un service générique pour tous les workflows
     */
    public function updateWorkflowAndFlush($picture, $transition): void
    {   
        $stateMachine = $this->workflows->get($picture, 'picture_publishing');
        
        if (!$stateMachine->can($picture, $transition)) {
            throw new \RuntimeException(sprintf('Transition "%s" is not valid for the current state', $transition));
        }
        
        $stateMachine->apply($picture, $transition);
        
        try {
           $this->manager->flush();                        
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la mise à jour du workflow' . $e->getMessage());
        }
    }

    /**
     * Création de l'image originale à partir des donnés binaires du fichier.
     * Utilisé en synchrone directement dans le ProductController 
     * Utilisé en  asynchrone dans UpdateFileMessageHandler.
     * 
     * La string de l'image est transmise par le message UpdateFileMessageHandler
     * 
     * @param string $file
     * @param Entity $pictureObjet
     */
    public function saveOriginalPictureFile(string $file, string $originalName): string
    {   
        $fileName = $originalName . '_' . $this->setUniqueName();
        // on transmet un string au format binary qui est converti en ressource GD
        $newGdRessource = imagecreatefromstring($file);
        // on crée un nouveau fichier webp à partir de la ressource.
        $imagewebp = imagewebp($newGdRessource, $this->picDir.'/'. $fileName, 80);
        // libérer la mémoire associée à la ressource GD une fois le fichier créé
        imagedestroy($newGdRessource);
        // on vérifie que le fichier a bien été créé
        if(!$imagewebp) {
            throw new \Exception('Le fichier n\'a pas pu être créé');
        }
        // le fichier est crée dans le dossier pictures
        // on retourne le nom du fichier pour l'utiliser dans toute la suite processus de redimentionnement
        return $fileName . '.webp'; // TODO à utiliser pour créer l'objet Picture dans la méthode $this->createPictureEntity()
    }

    /**
     * Upload d'images pour les produits (ajout et édition)
     * Utilisé en synchrone directement dans le ProductController 
     * Utilisé en  asynchrone dans UpdateFileMessageHandler.
     * @param string $name
     * @param string $alt
     * @param UploadFile $files
     * @param Entity $product (objet Product ou ID si messenger)
     */
    public function createProductPicture(string $name, string $alt, string $fileName, $product): void
    {   
        $files = $this->getOriginalPictureFile($fileName);

        foreach ($files as $file) {

            $picture = $this->createPictureEntity($name, $alt, $fileName, $product);
            
            if($picture) {
                // on met à jour le workflow de l'entité Picture
                $this->updateWorkflowAndFlush($picture, 'process');
            }
            // on envoie le fichier original au service ResizerService pour le redimentionner et le déplacer dans les différents dossiers
           $this->sendToResizerService($file, $fileName);
        }
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
    public function sendToResizerService($file, $fileName)
    {   
        $this->resizerService->cropAndMoveAllPictures($file, $fileName, 80);
        $this->resizerService->slider1280($file, $fileName, 50);
    }

    /**
     * Crée un objet Picture
     *
     * @param String $name
     * @param String $alt
     * @param String $fileName
     * @param Entity $product (objet Product ou ID si messenger)
     * @return Entity $picture
     */
    public function createPictureEntity(string $name, string $alt, string $fileName, $product): picture
    {   
        // On ne recherche pas le produit si c'est déjà un objet Product qui est reçu (ajout synchrone)
        // SI on reçoit un Id de produit, on recherche le produit en BDD (messenger - ajout asynchrone)
        if (!($product instanceof Product)) {
            $product = $this->manager->getRepository(Product::class)->find($product);
        }
        
        if (!$product) {
            throw new \Exception('Le produit n\'existe pas');
        }

        $picture = new Picture();
        $picture
            ->setName($name)
            ->setAlt($alt)
            ->setFileName($fileName)
            ->setProduct($product);

        $this->manager->persist($picture);

        return $picture;
    }
    
    /**
     * Récupère un fichier et crée un objet UploadedFile
     * pour le passer au service ResizerService dans le format attendu et à la clé 'file'.
     *
     * @param String $fileName
     * @return array
     */
    public function getOriginalPictureFile($fileName) : array
    {   
        $file = $this->picDir . '/' . $fileName;
        // on recrée un objet UploadedFile à partir du fichier original pour le passer au service ResizerService dans le format attendu.
        if($file){
            $file = new UploadedFile($file, $fileName, null, null, true);
            return ['file' => $file];
        } else {
            throw new \Exception('Le fichier ' . $fileName . ' n\'existe pas');
        }
    }

    /**
     * Uploade d'un fichier unique pour un document par ex .pdf
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

    // TODO a améliorer sur la gestion des repertoires
    /**
     * Supprime toutes les images de tous les dossiers de stockage
     * 
     * @return void
     */
    public function deletePicture($file)
    {   
        // on récupère le nom du fichier à supprimer
        $fileName = $file->getFileName();
       
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