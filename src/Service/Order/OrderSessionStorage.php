<?php

namespace App\Service\Order;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;

// Cette class gère la logique de stockage du panier en session.

class OrderSessionStorage
{   
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var Security
     */
    private $security;

    /**
     * The key used to store the cart id in session.
     * @var string
     */
    public const CART_KEY_NAME = 'cart_id';

    public function __construct(RequestStack $requestStack, OrderRepository $orderRepository, Security $security)
    {
        $this->requestStack = $requestStack;
        $this->orderRepository = $orderRepository;
        $this->security = $security;
    }

    /**
     * Gets the order in session.
     * @return Order|null
     */
    public function getOrder() : ?Order
    {    
        if ($this->security->getUser() && $this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->getUserLastOrder($this->security->getUser());
        } else {
            return $this->getAnanymeUserLastOrder();
        }
    }

    /**
     * Gets the last order for a logged in user.
     * If the user has an order in session, we return it.
     * @param User $user
     * @return Order|null
     */
    private function getUserLastOrder($user) : ?Order
    {   

        return $this->orderRepository->findOneBy([
            'id' => $this->getOrderId(), // on récupère l'id du panier en session
            'status' => Order::CART_STATUS,
            'user' => $user
        ]);
    }

    /**
     * Gets the last order for an anonymous user.
     * If the user has an order in session, we return it.
     * @return Order|null
     */
    private function getAnanymeUserLastOrder() : ?Order
    {
        return $this->orderRepository->findOneBy([
            'id' => $this->getOrderId(),
            'status' => Order::CART_STATUS,
            'userIdentifier' => $this->getSession()->get('user_identifier'),
        ]);
    }

    /**
     * Sets the order in session.
     * @param Order $order
     * @return void
     */
    public function setOrder(Order $order): void
    {   
        $this->getSession()->set(self::CART_KEY_NAME, $order->getId()); // je stocke l'id du panier en session (on a crée une card dans la BDD et on récupère son id)
        $this->getSession()->set('user_identifier', $order->getUserIdentifier()); // je récupère l'identifiant unique de l'utilisateur crée pour l"utilisateur lors de la création du panier en BDD
    }

    /**
     * Returns the order id.
     * @return int|null
     */
    private function getOrderId(): ?int
    {
        return $this->getSession()->get(self::CART_KEY_NAME);
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }

    /**
     * Removes the order from session.
     * @return void
     */
    public function removeCart(): void
    {
        $this->getSession()->remove(self::CART_KEY_NAME);
    }
}
