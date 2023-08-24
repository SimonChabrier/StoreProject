<?php

namespace App\Service\Order;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
// get user from session instead of injecting it
use Symfony\Component\Security\Core\Security;

/**
 * Class OrderFactory.
 */
class OrderFactory
{   
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Creates an order.
     */
    public function create(): Order
    {
        $order = new Order();

        $order
            ->setStatus(Order::CART_STATUS)
            ->setUser($this->security->getUser()) // on récupère l'utilisateur connecté - si pas de user connecté, le champ user sera null.
            ->setUserIdentifier(uniqid('user_')); // on génère un identifiant unique pour l'utilisateur qui nous permettra de retrouver son panier en session et/ou en bdd
                                                  // si il n'est pas connecté mais qu'il a déjà un panier en cours et se connecte ensuite ou crée un compte.
        
        return $order;
    }

    /**
     * Creates an item for a product.
     */
    public function createItem(Product $product): OrderItem
    {
        $item = new OrderItem();

        $item
            ->setProduct($product)
            ->setQuantity(1);

        return $item;
    }
}
