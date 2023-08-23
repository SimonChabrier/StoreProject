<?php

namespace App\Factory;

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

    public function getCurentUser()
    {
        return $this->security->getUser();
    }

    /**
     * Creates an order.
     */
    public function create(): Order
    {
        $order = new Order();
        $order
            ->setStatus(Order::STATUS_CART)
            ->setUser($this->getCurentUser())
            ->setUserIdentifier($this->createUniqueIdentifier())
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTime());

        return $order;
    }

    /**
     * Create unique user identifier.
     */
    public static function createUniqueIdentifier(): string
    {
        return uniqid('user_');
    }

    /**
     * Creates an item for a product.
     */
    public function createItem(Product $product): OrderItem
    {
        $item = new OrderItem();
        $item->setProduct($product);
        $item->setQuantity(1);

        return $item;
    }
}
