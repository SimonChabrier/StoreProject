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


    // all categories 
    /**
     * @Route("/categories", name="app_api_categories")
     */
    public function apiGetCategories(CategoryRepository $categoryRepository): Response
    {   
        return $this->json(
            $this->getDoctrine()->getRepository('App:Category')->findAll(),
            Response::HTTP_OK, 
            [], 
            ['groups' => 'category:read']
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
