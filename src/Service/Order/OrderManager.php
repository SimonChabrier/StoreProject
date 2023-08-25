<?php

namespace App\Service\Order;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\Order\OrderFactory;
use App\Service\Stock\StockManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Order\OrderSessionStorage;

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
     * @return void
     */
    public function save(Order $order): void
    {
        if ($order->getItems()->isEmpty()) {
            $this->removeOrder($order);
            return;
        }
        // on sauvegarde le panier en bdd
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        // on sauvegarde le panier en session pour maintenir l'état du panier à jour entre la bdd et la session
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
            $this->entityManager->remove($item);
            $this->entityManager->flush();
            // on sauvegarde l'état du panier en bdd et en session.
            $this->save($order);
        }
    }

    /**
     * Removes an order.
     * @param Order $order
     * @return void
     */
    private function removeOrder(Order $order): void
    {   
        // on supprime le panier en bdd
        $this->entityManager->remove($order);
        $this->entityManager->flush();
        // on supprime le panier en session
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
        // Mettre à jour le stock réservé
        $this->stockManager->reserveStock($order);
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
        // décrémenter le stock de chaque item
        $this->stockManager->decrementStock($order);
        // on libère du coup le stock réservé pour la commande
        $this->stockManager->releaseReservedStock($order);
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
