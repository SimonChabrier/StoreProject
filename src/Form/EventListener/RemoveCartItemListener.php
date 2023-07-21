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

    public function __construct(CartManager $cartManager)
    {
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
                $this->cartManager->deleteItem($cart, $child->getData());
                break;
            }
            // si save est cliqué sur un item, on met à jour la quantité de l'item
            if ($form->get('save')->isClicked()) {
                $this->cartManager->save($cart, $child->getData());
                break;
            }
        }
    }
}
