<?php

namespace App\Service;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Security\Core\Security;

class GlobalVarsService
{
    private $em;
    private $cache;
    private $security;

    public function __construct(EntityManagerInterface $em, CacheInterface $cache, Security $security)
    {
        $this->em = $em;
        $this->cache = $cache;
        $this->security = $security;
    }

    // retourne toutes les catégories pour le menu de navigation
    // déclaré dans twig comme un service injecté dans une variable 'catdAndSubCats' rendue globale..(voir config/twig.yaml)
    // la méthode est ensuite appellée dans twig comme ceci: {% for nav in catsAndSubCats.getNavCatsAndSubCats() %} 
    // pour cible directement la méthode sur la variable globale injectée dans tous les templates twig.

    public function getNavCatsAndSubCats() : ?array
    {   
        // $globalNavData = $this->em->getRepository(Category::class)->getOnlyCatAndOnlySubCat();
        // mettre en cache pour éviter de faire une requête à chaque fois
        $globalNavData = $this->cache->get('globalNav', function (ItemInterface $item) {
            $item->expiresAfter(3600);
            return $this->em->getRepository(Category::class)->getOnlyCatAndOnlySubCat();
        });
        return $globalNavData;

    }

    // retourne toutes les marques pour les filtres de la sidebar
    // déclaré dans twig comme un service injecté dans une variable 'brands' rendue globale..(voir config/twig.yaml)
    // la méthode est ensuite appellée dans twig comme ceci: {% for brand in brands.getBrands() %} 
    
    public function getBrands() : ?array
    {   
        //return $this->em->getRepository(Brand::class)->findBy([], ['name' => 'ASC']);
        // mettre en cache pour éviter de faire une requête à chaque fois
        $brands = $this->cache->get('globalBrands', function (ItemInterface $item) {
            $item->expiresAfter(3600);
            return $this->em->getRepository(Brand::class)->findBy([], ['name' => 'ASC']);
        });
        return $brands;
    }

    public function getUserOrder() : ?int
    {
        $user = $this->security->getUser();
        if(!$user) {
            return null;
        }
        //$order = $this->em->getRepository(Order::class)->findOneBy(['user' => $user, 'status' => Order::STATUS_CART]);
        //dump($order);
        $orderCount = $this->em->getRepository(Order::class)->count(['user' => $user, 'status' => Order::STATUS_CART]);
        //dd($orderCount);
        return $orderCount;
    }
}
