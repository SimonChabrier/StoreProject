<?php

namespace App\Service;

use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;
use App\Service\UploadService;

class ResizerService 
{   
    private $picture150Dir;
    private $picture250Dir;
    private $picture400Dir;
    private $picture1200Dir;
    private $slider1280Dir;

    

    public function __construct(
        string $picture150Dir, 
        string $picture250Dir, 
        string $picture400Dir, 
        string $picture1200Dir,
        string $slider1280Dir
        )
    {   
        $this->picture150Dir =  $picture150Dir;
        $this->picture250Dir =  $picture250Dir;
        $this->picture400Dir =  $picture400Dir;
        $this->picture1200Dir = $picture1200Dir;
        $this->slider1280Dir =  $slider1280Dir;
    }

    /**
     * Récupère le format de l'image et appelle la fonction adéquate
     * 
     * @param GD ressource $file
     * @return void
     */
    public function getFileFormat($file)
    {   
        switch ($file->getMimeType()) {
            case 'image/jpeg, image/pjpeg, image/jpg':
                return imagecreatefromjpeg($file);
            case 'image/png':
                return imagecreatefrompng($file);
            case 'image/gif':
                return imagecreatefromgif($file);
            case 'image/bmp':
                return imagecreatefrombmp($file);
            case 'image/webp':
                return imagecreatefromwebp($file);
            default:
                return imagecreatefromjpeg($file);
        }
    }

    /**
     * Crope l'image en l'étirant pour remplir le conteneur
     * source que j'ai adpaté : https://stackoverflow.com/questions/6891352/crop-image-from-center-php
     *
     * @param [GdImage] $img
     * @param int $cropWidth
     * @param int $cropHeight
     * @param string $horizontalAlign
     * @param string $verticalAlign
     * @return void
     */
    public function cropAndAlign($img, $cropWidth, $cropHeight, $horizontalAlign = 'center', $verticalAlign = 'middle') {
                
        // on récupère les dimensions de l'image originale
        $width = imagesx($img);
        $height = imagesy($img);
    
        // on calcule les proportions de l'image originale
        $proportions = $width / $height;
    
        // on calcule la largeur de l'image redimensionnée pour remplir le conteneur en conservant les proportions de l'image originale
        $newWidth = $cropWidth;
        $newHeight = $cropWidth / $proportions;
    
        // si la hauteur de l'image redimensionnée est plus petite que la hauteur du conteneur, on calcule la hauteur en conservant les proportions de l'image redimensionnée
        
        if ($newHeight < $cropHeight) {
            $newHeight = $cropHeight;
            $newWidth = $cropHeight * $proportions;
        }

        // on redimensionne l'image originale en fonction des dimensions calculées
        $resizedImg = imagecreatetruecolor($newWidth, $newHeight);
        //imagecopyresampled($resizedImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // on colle le contenu de l'image source dans l'image de destination en redimensionnant le contenu de l'image source
        // on récupère donc une novelle instance de l'image source redimensionnée (type : ressource GD)
        imagecopyresampled($resizedImg, // image de destination
                   $img, // image source
                   0, // coordonnée x de départ de la zone de destination
                   0, // coordonnée y de départ de la zone de destination
                   0, // coordonnée x de départ de la zone source
                   0, // coordonnée y de départ de la zone source
                   $newWidth, // largeur de la zone de destination
                   $newHeight, // hauteur de la zone de destination
                   $width, // largeur de la zone source
                   $height); // hauteur de la zone source
    
        // on calcule le nombre de pixels de décalage horizontal nécessaire pour aligner l'image
        $horizontalAlignPixels = self::calculatePixelsForAlign($newWidth, $cropWidth, $horizontalAlign);
    
        // on calcule le nombre de pixels de décalage vertical nécessaire pour aligner l'image
        $verticalAlignPixels = self::calculatePixelsForAlign($newHeight, $cropHeight, $verticalAlign);
    
        // on effectue le recadrage en fonction des valeurs de décalage horizontal et vertical calculées
        return imageCrop($resizedImg, [
            'x' => $horizontalAlignPixels[0],
            'y' => $verticalAlignPixels[0],
            'width' => $horizontalAlignPixels[1],
            'height' => $verticalAlignPixels[1]
        ]);
    }
    
    /**
     * Calcule le nombre de pixels de décalage horizontal ou vertical nécessaire pour aligner une image
     *
     * @param int $imageSize
     * @param int $cropSize
     * @param string $align
     * @return array
     */
    public function calculatePixelsForAlign($imageSize, $cropSize, $align) {
        
        // switch pour gérer les différentes options d'alignement possibles
       
        switch ($align) {
            // cas où l'alignement est à gauche ou en haut
            case 'left':
            case 'top':
                // on retourne un tableau contenant le nombre de pixels de décalage
                // horizontal ou vertical nécessaire pour l'alignement et la taille
                // de la zone de recadrage
                return [0, min($cropSize, $imageSize)];
            // cas où l'alignement est à droite ou en bas
            case 'right':
            case 'bottom':
                // on calcule le nombre de pixels de décalage horizontal ou vertical
                // nécessaire pour l'alignement en utilisant la taille de l'image et
                // la taille de la zone de recadrage
                return [max(0, $imageSize - $cropSize), min($cropSize, $imageSize)];
            // cas où l'alignement est au centre
            case 'center':
            case 'middle':
                // on calcule le nombre de pixels de décalage horizontal ou vertical
                // nécessaire pour l'alignement en utilisant la taille de l'image et
                // la taille de la zone de recadrage
                return [
                    max(0, floor(($imageSize / 2) - ($cropSize / 2))),
                    min($cropSize, $imageSize),
                ];
            // cas où l'alignement est invalide
            default:
                // on retourne un tableau contenant 0 pixels de décalage horizontal
                // et la taille de l'image pour la zone de recadrage
                return [0, $imageSize];
        }
    }

    public function cropAndMoveAllPictures($file, $fileName, $quality)
    {   
        // if(!$file instanceof UploadedFile) {
        //     throw new Exception('Le fichier n\'est pas une image');
        // }

        try {

            $img = self::getFileFormat($file);

            $sizeAndDirs = [
                'picture150Dir' => [150, 150],
                'picture250Dir' => [250, 250],
                'picture400Dir' => [400, 400],
                'picture1200Dir' => [1200, 1200],
            ];

            // On boucle sur le tableau pour créer les images dans les différents dossiers et les différents formats
            foreach ($sizeAndDirs as $dir => $size) {
                $newImg = self::cropAndAlign($img, $size[0], $size[1], 'center', 'middle');
                imagewebp($newImg, $this->{$dir} . '/' . $fileName, $quality);
            }

        } catch (Exception $e) {
                throw new Exception('Problème lors du redimensionnement de l\'image ' . $e->getMessage());
            }
    }

    /**
     * Ajoute une image dans le dossier slider1280
     *
     * @param UploadedFile $file
     * @param String $fileName
     * @param Int $quality
     * @return void
     */
    public function slider1280($file, $fileName, $quality)
    {   
        // faire un resize de l'image de 1280x720
        if(!$file instanceof UploadedFile) {
            throw new Exception('Le fichier n\'est pas une image');
        }
        
        try {
        $img = self::getFileFormat($file);
        imagewebp(self::cropAndAlign($img, 1600, 300, 'center', 'middle'), $this->slider1280Dir . '/' . $fileName, $quality);
        } catch (Exception $e) {
            throw new Exception('Problème lors du redimensionnement de l\'image ' . $e->getMessage());
        }
    }

}