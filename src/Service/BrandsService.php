<?php

namespace App\Service;

use App\Entity\Brand;
use Doctrine\ORM\EntityManagerInterface;

class BrandsService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // retourne toutes les marques pour les filtres de la sidebar
    // déclaré dans twig comme un service injecté dans une variable 'brands' rendue globale..(voir config/twig.yaml)
    // la méthode est ensuite appellée dans twig comme ceci: {% for brand in brands.getBrands() %} et les marques sont accèssibles dans tous les templates
    // puisque le template aside est inclus dans le template base.html.twig
    public function getBrands()
    {   
        return $this->em->getRepository(Brand::class)->findBy([], ['name' => 'ASC']);
    }
}
