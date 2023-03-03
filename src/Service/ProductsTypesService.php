<?php

namespace App\Service;

use App\Entity\ProductType;
use Doctrine\ORM\EntityManagerInterface;

class ProductsTypesService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // retourne toutes les marques pour les filtres de la sidebar
    // déclaré dans twig comme un service injecté dans une variable 'brands' rendue globale..(voir config/twig.yaml)
    // la méthode est ensuite appellée dans twig comme ceci: {% for productType in productsTypes.getProductsTypes() %} 
    
    public function getProductsTypes()
    {   
        return $this->em->getRepository(ProductType::class)->findBy([], ['name' => 'ASC']);
    }
}
