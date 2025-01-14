<?php

//https://symfony.com/doc/current/security.html#customizing-logout

namespace App\EventSubscriber\User;

use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LogoutSubscriber extends AbstractController implements EventSubscriberInterface 
{   

    public static function getSubscribedEvents(): array
    {
        return [LogoutEvent::class => 'onLogout'];
    }

    /**
     * @param LogoutEvent $event
     * @return void
     * 
     */
    public function onLogout(LogoutEvent $event): void
    {   
        // on récupère la session sur l'event et on la vide pour ne pas garder les données de l'utilisateur précédent (Eg : panier)
        $event->getRequest()->getSession()->invalidate();
        $this->addFlash('success', 'Déconnexion réussie!');
    }
}