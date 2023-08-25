<?php

namespace App\Service\Stock;

use App\Entity\Order;
use App\Entity\Product;
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
     * Vérifié que le produit est bien en stock sur l'attribut inStock
     */
    public function isProductInStock(Product $product) : bool
    {
        if($product->isInStock() === true) {
            return true;
        } else {
            return false;
        }
    }


//////// GESTION DU STOCK DES PRODUITS POUR LES COMMANDES ////////

    /** 
     * Réserve la quantité de produits pour une commande en cours
     * sans décrémenter le stock des produits mais en mettant à jour l'attribut reservedQuantity
     * pour chaque produit.
    */
    public function reserveStock(Order $order) : void
    {
        foreach ($order->getItems() as $item) {
            $product = $item->getProduct();
            $reservedQuantity = $item->getQuantity();

            // Mettre à jour la quantité réservée pour le produit
            $product->setReservedQuantity($product->getReservedQuantity() + $reservedQuantity);

            //TODO tester si la quantité en stock est suffisante pour la commande
            //TODO si la quantité en stock est insuffisante, on lève une exception

            if($product->getInStockQuantity() < $reservedQuantity) {
                throw new \Exception('La quantité en stock est insuffisante pour le produit ' . $product->getName());
            }

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

    /**
     * Décrémente le stock des produits si la commande est payée
     */
    public function decrementStock(Order $order) : void
    {
        foreach ($order->getItems() as $item) {
            $product = $item->getProduct();
            $quantity = $item->getQuantity();

            //TODO ici il faut gèrer le stock une foi que la commande est expédiée et que les produits sont sortis du dépôt.

            $product->setInStockQuantity($product->getInStockQuantity() - $quantity);

            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();
    }


//////// GESTION DU STOCK REEL (ENTREPOT) ////////

    /**
     * Incrémente le stock des produits si des produits sont reçu au dépot
     */
    public function incrementWarehouseProductStock(Product $product) : void
    {
        $product->setInStockQuantity($product->getInStockQuantity() + 1);

        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    /**
     * Décrémente le stock des produits si des produits sont sortis du dépot
     */
    public function decrementWarehouseProductStock(Product $product) : void
    {
        $product->setInStockQuantity($product->getInStockQuantity() - 1);

        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    /**
     * Incrémente le stock réservé des produits si des produits sont réservés au dépot
     */
    public function incrementWarehouseProductReservedStock(Product $product) : void
    {
        $product->setReservedQuantity($product->getReservedQuantity() + 1);

        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    /**
     * Décrémente le stock réservé des produits si des produits sont annulés au dépot
     */
    public function decrementWarehouseProductReservedStock(Product $product) : void
    {
        $product->setReservedQuantity($product->getReservedQuantity() - 1);

        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    /**
     * Incrémente la quantité en commande fournisseur (inSupplierOrderQuantity) des produits si des produits sont commandés au dépot
     */
    public function incrementProductInSupplierOrderQuantity(Product $product) : void
    {
        $product->setInSupplierOrderQuantity($product->getInSupplierOrderQuantity() + 1);

        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    /**
     * Décrémente la quantité en commande fournisseur (inSupplierOrderQuantity) des produits si des produits sont reçus au dépot
     */
    public function decrementProductInSupplierOrderQuantity(Product $product) : void
    {
        $product->setInSupplierOrderQuantity($product->getInSupplierOrderQuantity() - 1);

        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }


}
