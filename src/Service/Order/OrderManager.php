<?php

namespace App\Service\Order;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\OrderItem;
use App\Service\Order\OrderFactory;
use App\Service\Order\OrderSessionStorage;
use App\Service\Stock\StockManager;
use Doctrine\ORM\EntityManagerInterface;

// Cette class gère la logique métier du panier ici. 
// Les méthodes pour récupérer, sauvegarder et supprimer le panier sont ici.

class OrderManager
{
    /**
     * @var OrderSessionStorage
     */
    private $orderSessionStorage;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var StockManager
     */
    private $stockManager;

    /**
     * OrderManager constructor.
     */
    public function __construct(
        OrderSessionStorage $orderSessionStorage,
        OrderFactory $orderFactory,
        EntityManagerInterface $entityManager,
        StockManager $stockManager
    ) {
        $this->orderSessionStorage = $orderSessionStorage;
        $this->orderFactory = $orderFactory;
        $this->entityManager = $entityManager;
        $this->stockManager = $stockManager;
    }

    /**
     * Gets the current cart from session or creates a new one.
     * @return Order
     */
    public function getCurrentCart() : Order
    {   
        $order = $this->orderSessionStorage->getOrder();
        // si le panier n'existe pas en session, on le crée
        if (!$order) {
            $order = $this->orderFactory->create();
        }
        return $order;
    }

    /**
     * Get a order by id.
     * @param int $orderId
     * @return Order|null
    */
    public function getOrder(int $orderId) : ?Order
    {
        return $this->entityManager->getRepository(Order::class)->find($orderId);
    }

    /**
     * Saves an order in database and session.
     * @param Order $order
     */
    public function save(Order $order)
    {   
        if ($order->getItems()->isEmpty()) {
            $this->deleteOrder($order);
            return;
        }
        // Enregistrement du panier en base de données
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        // maintenant que le panier a un id, on peut le sauvegarder en session
        $this->orderSessionStorage->setOrder($order);
    }
    
    /**
     * Removes an item from an order.
     * @param Order $order
     * @param OrderItem $item
     * @return void
     */
    public function deleteItem(Order $order, OrderItem $item): void
    {
        if ($order->removeItem($item)) {
            $product = $item->getProduct();
            $onOrderQuantity = $item->getQuantity();
            
            // Décrémente la quantité réservée pour le produit
            $product->setOnOrderQuantity($product->getOnOrderQuantity() - $onOrderQuantity);
            
            // Supprimer l'item de l'ordre
            $this->entityManager->remove($item);
            $this->entityManager->flush();
            
            // Sauvegarder l'état du panier
            $this->save($order);
        }
    }


    /**
     * Removes an order.
     * @param Order $order
     * @return void
     */
    public function deleteOrder(Order $order): void
    {   
        // on récupère les items de la commande et on décrémente la quantité réservée pour chaque produit
        foreach ($order->getItems() as $item) {
            $product = $item->getProduct();
            $onOrderQuantity = $item->getQuantity();
            // Décrémente la quantité réservée pour le produit
            $product->setOnOrderQuantity($product->getOnOrderQuantity() - $onOrderQuantity);
        }
        
        // Supprimer le panier en bdd
        $this->entityManager->remove($order);
        $this->entityManager->flush();
        
        // Supprimer le panier en session
        $this->orderSessionStorage->removeCart();
    }


    ///////GESTION DU STOCK///////

    /**
     * Place an order and reserve the stock.
     *
     * @param Order $order
     * @return void
     */
    public function placeOrder(Order $order, string $orderStatus) : void
    {   // vérifier si le produit est en stock
        foreach($order->getItems() as $item){
            $this->stockManager->isProductInStock($item->getProduct());
        }
        // on met à jour le statut de la commande
        $order->setStatus($orderStatus);
        // Enregistrer la commande
        $this->save($order);
    }

    /**
     * Pay an order and decrement the stock.
     */
    public function payOrder(Order $order) : void
    {
        // réserver la quantité de chaque item
        $this->stockManager->reserveStock($order);
        // décrémenter le stock de chaque item
        $this->stockManager->decrementStock($order);;
        // on change le statut de la commande en "paid"
        $order->setStatus('paid');
        // on sauvegarde la commande en bdd
        $this->entityManager->persist($order);
        // si on flush 
        if($this->entityManager->flush()){
            // alors on supprime la commande de la session pour vider le panier de l'utilisateur
            $this->orderSessionStorage->removeCart();
        }
    }

    /**
     * Cancel an order and release the stock.
     *
     * @param Order $order
     * @return void
     */
    public function cancelOrder(Order $order) : void
    {
        // Libérer le stock réservé
        $this->stockManager->releaseReservedStock($order);
        // Annuler la commande
        $order->setStatus('cancelled');
        //TODO voir si il faudra supprimer la commande de la session ou pas ?

        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }
}
