<?php

namespace App\Service\Stock;

use App\Entity\Order;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

// Cette class gère la logique de gestion des stocks ici.

class StockManager
{
    private $entityManager;
    private $insufficientQuantityWarnings = [];

    public function __construct(EntityManagerInterface $entityManager, array $insufficientQuantityWarnings = [])
    {
        $this->entityManager = $entityManager;
        $this->insufficientQuantityWarnings = $insufficientQuantityWarnings;
    }


//////// UTILS ////////
    /**
     * Vérifie que le produit est bien en stock sur l'attribut inStock
    */
    public function isProductInStock(Product $product) : bool
    {
        if($product->isInStock() === true) {
            return true;
        } else {
              return false; // le produit n'est pas en stock et pas en commande fournisseur
            }
    }

    
    /**
     * Vérifie si le produit est en commande fournisseur sur l'attribut inSupplierOrder
    */
    public function isProductInSupplierOrder(Product $product) : bool
    {
        if($product->getInSupplierOrderQuantity() === true) {
            return true;
        } else {
            return false;
        }
    }

//////// GESTION DU STOCK DES PRODUITS POUR LES COMMANDES ////////


    /**
     * Retourne les warnings de quantité insuffisante pour la commande dans un tableau
     */
    public function getInsufficientQuantityWarnings(): array
    {
        return $this->insufficientQuantityWarnings;
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

            $product->setInStockQuantity($product->getInStockQuantity() - $quantity);

            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();
    }

    /**
     * Réserve la quantité de produits commandés si la commande est validée
     */
    public function reserveStock(Order $order) : void
    {
        foreach ($order->getItems() as $item) {
            $product = $item->getProduct();
            $quantity = $item->getQuantity();

            $product->setReservedQuantity($product->getReservedQuantity() + $quantity);

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
