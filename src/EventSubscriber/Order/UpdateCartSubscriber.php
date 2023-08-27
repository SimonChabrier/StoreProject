<?php

namespace App\EventSubscriber\Order;

use App\Entity\Order;
use App\Service\Order\OrderManager;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateCartSubscriber implements EventSubscriberInterface
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
     * Removes items from the cart based on the data sent from the user.
     */
    public function postSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $order = $form->getData();

        if (!$order instanceof Order) {
            return;
        }

        // On récupère tous les formulaires imbriqués dans le formulaire OrderType pour le champ items
        foreach ($form->get('items')->all() as $child) {
            if ($child->get('remove')->isClicked()) {
                $this->OrderManager->deleteItem($order, $child->getData());
                break;
            }
            // sur le formulaire parent on récupère le bouton update
            // si update est cliqué on met à jour la quantité de chaque item
            if ($form->get('update')->isClicked()) {
                $this->OrderManager->save($order);
                break;
            }
        }
    }
}