<?php

namespace App\Service\Order;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;

// Cette class gère la logique de gestion des stocks ici.

class StockManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /** 
     * Réserve la quantité de produits pour une commande en cours
    */
    public function reserveStock(Order $order) : void
    {
        foreach ($order->getItems() as $item) {
            $product = $item->getProduct();
            $reservedQuantity = $item->getQuantity();

            // Mettre à jour la quantité réservée pour le produit
            $product->setReservedQuantity($product->getReservedQuantity() + $reservedQuantity);

            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();
    }

    /**
     * Décrémente le stock si la commande est payée
     */
    public function decrementStock(Order $order) : void
    {
        foreach ($order->getItems() as $item) {
            $product = $item->getProduct();
            $quantity = $item->getQuantity();

            // Mettre à jour la quantité en stock
            $product->setInStockQuantity($product->getInStockQuantity() - $quantity);

            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();
    }

    /** 
     * Libère la quantité de produits réservés si la commande est annulée
    */
    public function releaseReservedStock(Order $order) : void
    {
        foreach ($order->getItems() as $item) {
            $product = $item->getProduct();
            $reservedQuantity = $item->getQuantity();

            // Libérer la quantité réservée pour le produit
            $product->setReservedQuantity($product->getReservedQuantity() - $reservedQuantity);

            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();
    }


}
