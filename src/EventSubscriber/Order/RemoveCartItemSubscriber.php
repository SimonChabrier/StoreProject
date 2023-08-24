<?php

namespace App\EventSubscriber\Order;

use App\Entity\Order;
use App\Service\Order\CartManager;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RemoveCartItemSubscriber implements EventSubscriberInterface
{   
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

        // On récupère tous les formulaires imbriqués dans le formulaire CartType pour le champ items
        foreach ($form->get('items')->all() as $child) {
            if ($child->get('remove')->isClicked()) {
                $this->cartManager->deleteItem($cart, $child->getData());
                // méthode originale déclaré dans l'entité mais elle ne supprime pas l'item de la BDD 
                // $cart->removeItem($child->getData());
                break;
            }
            // sur le formulaire parent on récupère le bouton save
            // si save est cliqué on met à jour la quantité de chaque item
            // save = bouton mettre à jour le panier.
            if ($form->get('save')->isClicked()) {
                $this->cartManager->save($cart);
                break;
            }
        }
    }
}
