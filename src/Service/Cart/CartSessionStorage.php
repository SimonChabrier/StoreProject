<?php

namespace App\Service\Cart;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartSessionStorage
{
    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private $requestStack;

    /**
     * The cart repository.
     *
     * @var OrderRepository
     */
    private $cartRepository;

    /**
     * @var string
     * Le nom de la clé en session qui contient le panier courant
     */
    const CART_KEY_NAME = 'cart_id';

    /**
     * CartSessionStorage constructor.
     *
     * @param RequestStack $requestStack
     * @param OrderRepository $cartRepository
     */
    public function __construct(RequestStack $requestStack, OrderRepository $cartRepository)
    {
        $this->requestStack = $requestStack;
        $this->cartRepository = $cartRepository;
    }

    /**
     * Récupère le panier courant en session.
     *
     * @return Order|null
     */
    public function getCart(): ?Order
    {
        return $this->cartRepository->findOneBy([
            'id' => $this->getCartId(),
            'status' => Order::STATUS_CART
        ]);
    }

    /**
     * Ajoute le panier courant en session.
     *
     * @param Order $cart
     */
    public function setCart(Order $cart): void
    {
        $this->getSession()->set(self::CART_KEY_NAME, $cart->getId());
    }

    /**
     * Retourne l'id du panier courant en session.
     *
     * @return int|null
     */
    private function getCartId(): ?int
    {
        return $this->getSession()->get(self::CART_KEY_NAME);
    }

    /**
     * Retourne la session.
     *
     * @return SessionInterface
     */
    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }
}