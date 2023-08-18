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
        return md5 (uniqid());
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
            // Log or handle the exception appropriately
            throw $e;
        }
    }

    /**
     * Upload d'images pour les produits (ajout et édition)
     * 
     * @param string $file
     */
    public function uploadProductPictures(string $name, string $alt, $filesData, $product): void
    {   
        foreach ($filesData as $fileData) {
            // ici on travaille sur le fichier qu'on recrée dans getTempFile()
            $fileName = $fileData->getClientOriginalName();
            $this->moveAll($fileData, $fileName, 80);
            $this->moveOriginalFile($fileData, $fileName);
            $this->createPicture($name, $alt, $fileName, $product);
        }
    }

    /**
     * Crée un objet Picture
     *
     * @param String $name
     * @param String $alt
     * @param String $fileName
     * @param Entity $product (objet Product ou ID si messenger)
     * @return void
     */
    public function createPicture(string $name, string $alt, string $fileName, $product): void
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
        $this->updateWorkflowAndFlush($picture, 'process');
    }


    public function moveOriginalFile($fileData, $fileName): void
    {   
        $fileData->move($this->picDir, $fileName);
    }

    public function createTempFile($fileData) : string
    {
        // on récupère le nom du fichier original sans l'extension
        $originalFileName = pathinfo($fileData->getClientOriginalName(), PATHINFO_FILENAME);
        // on génère un nom de fichier unique et on ajoute l'extension .webp
        $fileName = $originalFileName . '_' . $this->setUniqueName() . '.webp';
        // on déplace le fichier dans le dossier temporaire
        $fileData->move($this->picDir, $fileName);
        // on retourne le nom du fichier pour l'utiliser dans toute la suite processus de redimentionnement
        return $fileName;
    }

    public function getTempFile($fileName) : array
    {   
        $picture = $this->picDir.'/'.$fileName;
        // on recrée un objet UploadedFile à partir du fichier original pour le passer au service ResizerService dans le format attendu.
        if($picture){
            return $this->createNewUploadedFile($picture, $fileName);
        } else {
            // exception
            throw new \Exception('Le fichier n\'existe pas');
        }
    }

    /**
     * Crée un objet UploadedFile à partir d'un fichier
     * 
     * @param string $picture
     * @param string $fileName
     * @return array
     */
    public function createNewUploadedFile($picture, $fileName){
        $picture = new UploadedFile($picture, $fileName, null, null, true);
        return ['file' => $picture];
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