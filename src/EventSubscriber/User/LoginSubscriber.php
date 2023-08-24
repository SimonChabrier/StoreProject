<?php

namespace App\EventSubscriber\User;

use App\Service\Order\CartManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrderRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


class LoginSubscriber extends AbstractController implements EventSubscriberInterface 
{   

    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private $entityManager;

    /**
     * The order repository.
     *
     * @var OrderRepository
     */
    private $orderRepository;

    public function __construct(EntityManagerInterface $entityManager, OrderRepository $orderRepository)
    {
        $this->entityManager = $entityManager;
        $this->orderRepository = $orderRepository;
    }



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

        // on récupère l'utilisateur connecté en utilisant le token de l'event (qui contient l'utilisateur)
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();
        
        // on récupère l'identifiant de l'utilisateur en session (qui est l'identifiant de l'utilisateur avant qu'il ne se connecte)
        // cet identifiant est associé au panier en BDD si l'utilisateur n'était pas connecté avant de se connecter et qu'il avait un panier en session et donc aussi en BDD.
        $userIdentifier = $event->getRequest()->getSession()->get('user_identifier');

        if(!$userIdentifier){
            $this->addFlash('success', 'Connexion réussie');
        }
        // on ajoute l'utilisateur au panier existant en bdd qui n'avait pas de user associé 
        // mais qui correspond à la valeur de la clé user_identifier en session enregistrée sur l'attribut userIdentifier de la table Order.
        if($userIdentifier){
            $cart = $this->orderRepository->findOneBy([
                'userIdentifier' => $userIdentifier
            ]);

            if($cart){
                // on met à jour le panier en bdd avec l'utilisateur connecté
                $cart->setUser($user);
                $this->entityManager->flush();
                $this->addFlash('success', 'Connexion réussie vous avez une commande en cours.');
            }
        }
    }
}