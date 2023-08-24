<?php

namespace App\Service\Order;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\Order\OrderFactory;
use App\Service\Order\OrderSessionStorage;
use Doctrine\ORM\EntityManagerInterface;

class OrderManager
{
    /**
     * @var OrderSessionStorage
     */
    private $OrderSessionStorage;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * OrderManager constructor.
     */
    public function __construct(
        OrderSessionStorage $cartStorage,
        OrderFactory $orderFactory,
        EntityManagerInterface $entityManager
    ) {
        $this->OrderSessionStorage = $cartStorage;
        $this->orderFactory = $orderFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * Gets the current cart.
     */
    public function getCurrentCart()
    {   

        $cart = $this->OrderSessionStorage->getCart();
        // dump($cart);
        // si le panier n'existe pas en session, on le crée
        if (!$cart) {
            $cart = $this->orderFactory->create();
        }

        return $cart;
    }

    /**
     * Sauvegarde l'état du panier en session et en base de données.
     */
    public function save(Order $cart): void
    {   

        // si le panier est vide alors on supprime le panier de la BDD et de la session
        // on recréera un nouveau panier vide à la prochaine requête...
        if($cart->getItems()->isEmpty()){
            $this->entityManager->remove($cart);
            $this->entityManager->flush();

            // on supprime aussi le panier de la session
            $this->OrderSessionStorage->removeCart();

            return;
        }

        $this->entityManager->persist($cart);
        $this->entityManager->flush();
        // session placer après le flush pour avoir l'id du panier
        // sinon on a une erreur car le panier n'a pas d'id
        $this->OrderSessionStorage->setCart($cart);
        
    }

    /**
     * Removes an item from the cart (database and session).
     *
     * @param Order      $cart
     * @param OrderItem  $item
     */
    public function deleteItem(Order $cart, OrderItem $item): void
    {   
        // Remove the item from the cart
        if($cart->removeItem($item)){
            $this->entityManager->remove($item);
            $this->entityManager->flush();
        }

        $this->save($cart);
        
    }

}
