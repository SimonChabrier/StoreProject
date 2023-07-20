<?php

namespace App\Service\Cart;

use App\Entity\Order;
use App\Service\Order\OrderFactory;
use App\Service\Cart\CartSessionStorage;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class CartManager
 * @package App\Service\Cart
 */
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
     *
     * @param CartSessionStorage $cartStorage
     * @param OrderFactory $orderFactory
     * @param EntityManagerInterface $entityManager
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
     * 
     * @return Order
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
     * Persists the cart in database and session.
     *
     * @param Order $cart
     */
    public function save(Order $cart): void
    {
        // On sauve en BDD
        $this->entityManager->persist($cart);
        $this->entityManager->flush();
        // On sauve en session
        $this->cartSessionStorage->setCart($cart);
    }
}