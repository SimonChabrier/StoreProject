<?php

namespace App\Service\Order;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
// get user from session instead of injecting it
use Symfony\Component\Security\Core\Security;

class OrderSessionStorage
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
     * @var Security
     */
    private $security;

    /**
     * @var string
     */
    public const CART_KEY_NAME = 'cart_id';

    /**
     * OrderSessionStorage constructor.
     */
    public function __construct(RequestStack $requestStack, OrderRepository $cartRepository, Security $security)
    {
        $this->requestStack = $requestStack;
        $this->cartRepository = $cartRepository;
        $this->security = $security;
    }

    /**
     * Gets the cart in session.
     */
    public function getCart()
    {   
        // si l'utilisateur est connecté, on récupère les panier en cours de cet utilisateur uniquement
        // on vérifie si il est connecté
        if($this->security->getUser() && $this->security->isGranted('IS_AUTHENTICATED_FULLY')){
            $currentUserLastOrder = $this->cartRepository->findOneBy([
                'status' => Order::CART_STATUS, //TODO il faudra gérer les status des commandes après (new, paid, shipped, delivered, canceled etc...)
                'user' => $this->security->getUser()
            ]);

            return $currentUserLastOrder;

        } else {
            // soit il n'est pas connecté et on récupère l'identifiant unique de l'utilisateur en session
            // ici j'ai un identifiant unique de l'utilisateur en session
            // il faut que je récupère le panier en cours de cet utilisateur uniquement
            $currentUserLastOrder = $this->cartRepository->findOneBy([
                'id' => $this->getCartId(),
                'status' => Order::CART_STATUS, //TODO il faudra gérer les status des commandes après (new, paid, shipped, delivered, canceled etc...)
                'userIdentifier' => $this->getSession()->get('user_identifier'),
            ]);

            return $currentUserLastOrder;
        }
    }

    /**
     * Sets the cart in session.
     */
    public function setCart(Order $cart): void
    {   
        $this->getSession()->set(self::CART_KEY_NAME, $cart->getId());
        // je récupère l'identifiant unique de l'utilisateur crée pour l"utilisateur 
        // lors de la création du panier en BDD
        $this->getSession()->set('user_identifier', $cart->getUserIdentifier());
    }

    /**
     * Returns the cart id.
     */
    private function getCartId(): ?int
    {
        return $this->getSession()->get(self::CART_KEY_NAME);
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }

    /**
     * Removes the cart from session.
     */
    public function removeCart(): void
    {
        $this->getSession()->remove(self::CART_KEY_NAME);
    }
}
