<?php

namespace App\Form\EventListener;

use App\Entity\Order;
use App\Manager\CartManager;
use App\Storage\CartSessionStorage; 
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RemoveCartItemListener implements EventSubscriberInterface
{   
    private $cartSessionStorage;
    private $cartManager;

    public function __construct(CartSessionStorage $cartSessionStorage, CartManager $cartManager)
    {
        $this->cartSessionStorage = $cartSessionStorage;
        $this->cartManager = $cartManager;
    }
    
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [FormEvents::POST_SUBMIT => 'postSubmit'];
    }

    /**
     * Removes items from the cart based on the data sent from the user.
     */
    public function postSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $cart = $form->getData();

        if (!$cart instanceof Order) {
            return;
        }

        // Removes items from the cart
        foreach ($form->get('items')->all() as $child) {
            if ($child->get('remove')->isClicked()) {
                $cart->removeItem($child->getData());  // Remove the item from the cart
                $this->cartSessionStorage->removeItemFromSession($child->getData());
                $this->cartManager->removeItemFromCart($cart, $child->getData());
                break;
            }
        }
    }
}
