<?php

namespace App\EventSubscriber\Order;

use App\Entity\Order;
use App\Service\Order\OrderManager;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClearCartSubscriber implements EventSubscriberInterface
{   
    private $OrderManager;

    public function __construct(OrderManager $OrderManager)
    {
        $this->OrderManager = $OrderManager;
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
     * Delete the cart from the database and the session.
     */
    public function postSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $order = $form->getData();

        if (!$order instanceof Order) {
            return;
        }

        // Is the clear button clicked?
        if (!$form->get('clear')->isClicked()) {
            return;
        }

        // Clears the cart
        $order->removeItems();
        // Clear the cart from the database and the session
        $this->OrderManager->save($order);
    }
}

