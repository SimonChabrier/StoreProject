<?php

namespace App\Service;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

class NavService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // retourne toutes les catégories pour le menu de navigation
    // déclaré dans twig comme un service injecté dans une variable 'nav' rendue globale..(voir config/twig.yaml)
    // la méthode est ensuite appellée dans twig comme ceci: {% for nav in nav.getNav() %} et la nav est accèssible dans tous les templates
    // puisque le template nav est inclus dans le template base.html.twig
    public function getNav()
    {   
        //dump($this->em->getRepository(Category::class)->requestNav());
        return $this->em->getRepository(Category::class)->requestNav();
    }
}
