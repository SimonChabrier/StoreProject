<?php

namespace App\Manager;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Factory\OrderFactory;
use App\Storage\CartSessionStorage;
use Doctrine\ORM\EntityManagerInterface;

class CartManager
{
    /**
     * @var CartSessionStorage
     */
    private $cartSessionStorage;

    /**
     * @var OrderFactory
     */
    private $cartFactory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * CartManager constructor.
     */
    public function __construct(
        CartSessionStorage $cartStorage,
        OrderFactory $orderFactory,
        EntityManagerInterface $entityManager
    ) {
        $this->cartSessionStorage = $cartStorage;
        $this->cartFactory = $orderFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * Gets the current cart.
     */
    public function getCurrentCart(): Order
    {
        $cart = $this->cartSessionStorage->getCart();
        // si le panier n'existe pas en session, on le crée
        if (!$cart) {
            $cart = $this->cartFactory->create();
        }

        return $cart;
    }

    /**
     * Sauvegarde l'état du panier en session et en base de données.
     */
    public function save(Order $cart): void
    {   
        // database
        $this->entityManager->persist($cart);
        $this->entityManager->flush();
        // session
        $this->cartSessionStorage->setCart($cart);
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
        $cart->removeItem($item);
        $this->save($cart);
    }
}
