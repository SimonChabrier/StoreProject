<?php

namespace App\Form\EventListener;

use App\Entity\Order;
use Symfony\Component\Console\Helper\Dumper;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RemoveCartItemListener implements EventSubscriberInterface
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
                break;
            }
        }
    }
}
