<?php

namespace App\Form\EventListener;

use App\Entity\Order;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClearCartListener implements EventSubscriberInterface
{   
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [FormEvents::POST_SUBMIT => 'postSubmit'];
    }

    /**
     * Removes all items from the cart when the clear button is clicked.
     */
    public function postSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $cart = $form->getData();

        if (!$cart instanceof Order) {
            return;
        }

        // Is the clear button clicked?
        if (!$form->get('clear')->isClicked()) {
            return;
        }

        // Clears the cart
        $cart->removeItems();

         // Clear the session after processing the form
         $this->session->clear();
    }
}
