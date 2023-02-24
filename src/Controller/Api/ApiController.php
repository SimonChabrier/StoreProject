<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
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
     * @Route("/products", name="app_api")
     */
    public function apiGet(ProductRepository $pr): Response
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
}
