<?php

namespace App\Service;

use App\Service\ClearCacheService;
use App\Repository\PictureRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class DeleteFileService
{   
    private $fileSystem;
    private $pictureRepository;
    private $cache;
    private $clearCacheService;

    const BASE_PATH = '../public/uploads/files/';
    const CACHE_KEY = 'home_data';

    public function __construct(
        Filesystem $fileSystem, 
        PictureRepository $pictureRepository, 
        AdapterInterface $cache, 
        ClearCacheService $clearCacheService
        )
    {   
        $this->fileSystem = $fileSystem;
        $this->pictureRepository = $pictureRepository;
        $this->cache = $cache;
        $this->clearCacheService = $clearCacheService;
    }
    
    public function deleteAllPictures()
    {

        $directories = [
            'pictures',
            'pictures_XS',
            'pictures_250',
            'pictures_400',
            'pictures_1200',
            'slider_1280',
        ];

        // Supprimer les fichiers
        $filesToDelete = [];

        foreach ($directories as $directory) {
            $files = glob(self::BASE_PATH . $directory . '/*');
            // on merge les tableaux pour avoir un seul tableau avec tous les fichiers à supprimer
            // sinon on aurait un tableau par répertoire et il faudrait faire une boucle pour chaque répertoire
            // pour fournir des chemins de fichiers à supprimer à la méthode remove de la classe Filesystem
            $filesToDelete = array_merge($filesToDelete, $files);
        }

        // le nombre total de fichiers à supprimer dans les répertoires locaux
        $localFileCount = count($filesToDelete);
        // on supprime les fichiers
        $this->fileSystem->remove($filesToDelete);

        // Supprimer les enregistrements de base de données
        $pictures = $this->pictureRepository->findAll();

        // le nombre total d'enregistrements de fichiers base de données
        $databasePicturesCount = count($pictures);

        // on initialise un compteur pour compter le nombre de fichiers supprimés
        $deletedPictureCount = 0;

        foreach ($pictures as $picture) {
            // true pour que ça flush directement en base de données
            $this->pictureRepository->remove($picture, true);
            // on ajoute 1 à chaque fois qu'on supprime un enregistrement
            $deletedPictureCount++;
        }

        // on vérifie qu'on a fait autant de tour de boucle de supression qu'il y a des fichier en BDD avec $deletedPictureCount === $databasePicturesCount
        // on vérifie aussi que chaque fichier enregistré en BDD était bien dans chaque répertoire local avec $localFileCount === $databasePicturesCount * count($directories)
        if ($deletedPictureCount === $databasePicturesCount && $localFileCount === $databasePicturesCount * count($directories)) {
            // on supprime le cache et on refait le json
            $this->clearCacheService->clearCacheAndJsonFile(self::CACHE_KEY);
            return true;
        } else {
            throw new \Exception('Erreur lors de la suppression des images');
        }
        
    }
}