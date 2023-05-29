<?php

namespace App\Service;

use App\Entity\Picture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

//use App\Entity\Picture;
//use Doctrine\ORM\EntityManagerInterface;

class UploadService
{   
    // certaines propriétés sont ajoutés ici parce que comme elles sont bindées dans services.yaml
    // il faut les ajouter dans au moins un service pour générer d'erreur de Symfony.
    private $adminEmail;
    private $picDir;
    private $docDir;
    private $slugger;


    public function __construct($adminEmail, string $picDir, string $docDir, SluggerInterface $slugger)
    {
        $this->adminEmail = $adminEmail;
        $this->picDir = $picDir;
        $this->docDir = $docDir;
        $this->slugger = $slugger;
    }
    /**
     * Upload d'une image unique
     * Non utilisé actuellement
     *
     * @param file $file
     * @param Entity $pictureObjet
     * @return Picture
     */
    public function uploadPicture($file, $pictureObjet): Picture
    {   
        $fileName = md5(uniqid()).'.'.$file->guessExtension();
        $file->move($this->picDir, $fileName);
        // on retourne l'objet Picture avec le nom du fichier créee pour le stocker en BDD
        // car ce n'est pas l'utilisateur qui va le saisir on crée ici un nom de fichier unique.
        return $pictureObjet->setFileName($fileName);
    }

    /**
     * Uplaod d'une collection d'images
     *
     * @param array $filesArray
     * @param Entity $pictureObjet
     * @param Entity $galleryObject
     * @return Picture
     */
    public function uploadPictures(array $filesArray, $pictureObjet, $productObject): Object
    {   

        foreach($filesArray as $file) {

            $fileName = md5(uniqid()).'.'.$file->guessExtension();
            $file->move($this->picDir, $fileName);
            // A chaque itération, on initialise les propriétés de l'objet Picture avec les infos du formulaire
            $pictureObjet
                         ->setName($pictureObjet->getName())
                         ->setAlt($pictureObjet->getAlt())
                         ->setFileName($fileName)
                         ->setProduct($productObject);
        }
       
        // A chaque itération, on retourne l'objet Picture initialisé avec un nom de fichier unique pour le stocker en BDD 
        // utlisé ensuite pour construire l'affichage du fichier dans la vue.
        return $pictureObjet;
    }


    public function uploadFile($file, $fileObject)
    {   
        $fileName = md5(uniqid()).'.'.$file->guessExtension();
        $file->move($this->docDir, $fileName);
        return $fileObject->setFileName($fileName);
    }

    public function uploadFiles(array $filesArray, $fileObject, $productObject)
    {   
        foreach($filesArray as $files) {

            $fileName = md5(uniqid()).'.'.$files->guessExtension();
            $files->move($this->picDir, $fileName);
            // A chaque itération, on initialise les propriétés de l'objet File avec les infos du formulaire
            $fileObject->setProduct($productObject)
                         ->setName($fileObject->getName())
                         ->setInfo($fileObject->getInfo())
                         ->setFileName($fileName);
        }
        // A chaque itération, on retourne l'objet File initialisé avec un nom de fichier unique pour le stocker en BDD 
        // utlisé ensuite pour construire l'affichage du fichier dans la vue.
        return $fileObject;
    }


}
