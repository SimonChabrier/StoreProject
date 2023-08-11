<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{   

    /**
     * @Route("/categories", name="app_api_categories")
     */
    public function apiGetCategories(CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    {    
        // on récupère les catégories qui ont showOnHome = true
        $cats = $categoryRepository->findBy(['showOnHome' => 'true'], ['listOrder' => 'ASC']);
        // on va stocker les données dans un tableau
        $data = [];
        
        // on boucle sur les catégories
        foreach ($cats as $category) {
            $subCategories = $category->getSubCategories();
            
            // on va stocker les données de la catégorie dans un tableau
            $categoryData = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'subCategories' => [],
            ];
            // on boucle sur les sous-catégories
            foreach ($subCategories as $sub) {
                $products = $productRepository->findBy(['subCategory' => $sub->getId(), 'visibility' => 'true'], ['id' => 'DESC'], 4);
                // on va stocker les données de la sous-catégorie dans un tableau
                $subCategoryData = [
                    'id' => $sub->getId(),
                    'name' => $sub->getName(),
                    'products' => $products,
                ];
                // on ajoute les données de la sous-catégorie au tableau de la catégorie
                $categoryData['subCategories'][] = $subCategoryData;
            }
            // on ajoute les données de la catégorie au tableau des données
            $data[] = $categoryData;
        }
    
            return $this->json(
                $data,
                Response::HTTP_OK, 
                [], 
                ['groups' => 'product:read']
            );
    }

    /**
     * @Route("/products", name="app_api_products")
     */
    public function apiGetProducts(ProductRepository $pr): Response
    {   
        return $this->json(
            // find in  products inStockQuantity = true or > 0
            $pr->findBy(['inStockQuantity' => true], ['createdAt' => 'ASC']),
            Response::HTTP_OK, 
            [   // count the number of products in stock
                'info' => count($pr->findBy(['inStockQuantity' => true]))
            ], 
            ['groups' => 'product:read']
        );
    }

    // par id de category 
    /**
     * @Route("/products/{id}", name="app_api_product")
     */
    public function apiGetProduct(Product $product): Response
    {
        return $this->json(
            $product,
            Response::HTTP_OK, 
            [], 
            ['groups' => 'product:read']
        );
    }
}
