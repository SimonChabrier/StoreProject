<?php

namespace App\Service\Order;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\OrderItem;

/**
 * Class OrderFactory
 * @package App\Service\Order
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
        // si la quantité est négative, on la met à 0
        if ($item->getQuantity() < 0) {
            $item->setQuantity(0);
        }
        // TODO vérifier l'état des stocks avant de créer l'item.
        // $product->setinStockQuantity($product->getinStockQuantity() - $item->getQuantity());

        // sinon on met la quantité du produit
        $item->setQuantity(1);

        return $item;
    }
}