<?php

namespace App\Service;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class GlobalVarsService
{
    private $em;
    private $cache;
    private $security;
    private $requestStack;

    public function __construct(EntityManagerInterface $em, CacheInterface $cache, Security $security, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->cache = $cache;
        $this->security = $security;
        $this->requestStack = $requestStack;
    }

    // retourne toutes les catégories pour le menu de navigation
    // déclaré dans twig comme un service injecté dans une variable 'catdAndSubCats' rendue globale..(voir config/twig.yaml)
    // la méthode est ensuite appellée dans twig comme ceci: {% for nav in catsAndSubCats.getNavCatsAndSubCats() %} 
    // pour cible directement la méthode sur la variable globale injectée dans tous les templates twig.

    /**
     * Returns the categories and subcategories for the navigation menu.
     */
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

    /**
     * Returns the brands for the sidebar filters.
     */
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

    /**
     * Returns the number of items in the cart for the current user.
     */
    public function getUserOrder() : ?int
    {
        $user = $this->security->getUser();
        $userIdentifier = $this->getSession()->get('user_identifier');
        
        dump($user);
        dump($userIdentifier);

        if(!$user && !$userIdentifier) {
            return null;
        }
        // si je n'ai pas de user mais que j'ai un userIdentifier en session, je récupère le nombre de paniers 'new' de cet utilisateur anonyme
        if(!$user && $userIdentifier) {
            $orderCount = $this->em->getRepository(Order::class)->count(['userIdentifier' => $userIdentifier, 'status' => Order::CART_STATUS]); // CART_STATUS === 'new'
            return $orderCount;
        }
        // si le user est connecté, je récupère le nombre de paniers 'new' de cet utilisateur
        if($user) {
            $orderCount = $this->em->getRepository(Order::class)->count(['user' => $user, 'status' => Order::CART_STATUS]); // CART_STATUS === 'new'
            return $orderCount;
        }

        return null;
    }

    /**
     * Returns the session.
     */
    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }
}
