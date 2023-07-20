<?php

namespace App\Service\Order;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\OrderItem;

/**
 * Class OrderFactory
 * @package App\Factory
 */
class OrderFactory
{
    /**
     * Crée une commande.
     *
     * @return Order
     */
    public function create(): Order
    {
        $order = new Order();
        $order
            ->setStatus(Order::STATUS_CART);
            // géré par Gedmo Timestampable (voir src/Entity/Order.php)
            // ->setCreatedAt(new \DateTimeImmutable())
            // ->setUpdatedAt(new \DateTime());

        return $order;
    }

    /**
     * Crée un item de commande pour un produit donné. 
     * (une ligne de la commande pour un produit donné)
     *
     * @param Product $product
     *
     * @return OrderItem
     */
    public function createItem(Product $product): OrderItem
    {
        $item = new OrderItem();
        $item->setProduct($product);
        $item->setQuantity(1);

        return $item;
    }
}