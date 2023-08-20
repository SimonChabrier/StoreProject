<?php

namespace App\EventSubscriber\User;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;


class LoginSubscriber extends AbstractController implements EventSubscriberInterface 
{

    public static function getSubscribedEvents(): array
    {
        return [InteractiveLoginEvent::class => 'onLogin'];
    }

    /**
     * @param InteractiveLoginEvent $event
     * @return void
     */
    public function onLogin(InteractiveLoginEvent $event): void
    {
        $this->addFlash('success', 'Connexion réussie!');
    }
}