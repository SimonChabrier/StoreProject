<?php

namespace App\Service\Cache;


class SetProduct {

    public function __construct()
    {   
    }

    /**
     * Pour chaque produit, on récupère les données dont on a besoin et on les stocke dans un tableau
     * le cache ne peut pas stocker les objets Doctrine, il faut donc les transformer en tableau
     * sinon on a une erreur de type : "Object of class App\Entity\Product could not be converted to string"
     * Si on ajoute à la récupèration des données une serialization pour les mettre en cache et une deserialization pour les afficher
     * on va consommer plus de ressources serveur pour rien, donc on ne le fait pas.
     * 
     * @return array $productsData
     */
    public function setProductData($products): array
    {    
        $productsData = [];

        foreach ($products as $product) {

            $productData = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'category' => $product->getCategory() ? $product->getCategory()->getName() : null,
                'pictures' => [], // on ne récupère que la première image du tableau : $product->getPictures()[0
                'catalogPrice' => $product->getCatalogPrice(),
                'sellingPrice' => $product->getSellingPrice(),
                'subCategory' => $product->getSubCategory()->getName(),
                'productType' => $product->getProductType()->getName(),
                'brand' => $product->getBrand()->getName(),
                'visibility' => $product->getVisibility(),
                'isInStock' => $product->getIsInStock(),
            ];
            // récupèrer les images du produit
            foreach ($product->getPictures() as $picture) {
                // on a besoin du nom du alt et du fileName
                $productData['pictures'][] = [
                    'id' => $picture->getId(),
                    'alt' => $picture->getAlt(),
                    'fileName' => $picture->getFileName(),
                ];
            }
            // on stocke les produits dans le tableau : 'products' => [], de la catégorie
            $productsData[] = $productData;
        }
        return $productsData;
    }


}