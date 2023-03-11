<?php

namespace App\Service;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

class CatsAndSubCatsService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // retourne toutes les catégories pour le menu de navigation
    // déclaré dans twig comme un service injecté dans une variable 'catdAndSubCats' rendue globale..(voir config/twig.yaml)
    // la méthode est ensuite appellée dans twig comme ceci: {% for nav in catsAndSubCats.getOnlyCatsAndOnlySubCats() %} 
    // pour cible directement la méthode sur la variable globale injectée dans tous les templates twig.

    public function getOnlyCatsAndOnlySubCats()
    {   
        dd('test');
        dump($this->em->getRepository(Category::class)->getOnlyCatAndOnlySubCat());
        return $this->em->getRepository(Category::class)->getOnlyCatAndOnlySubCat();
    }
}
