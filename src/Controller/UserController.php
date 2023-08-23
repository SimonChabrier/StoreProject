<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


/**
 * @Route("/user")
 */
class UserController extends AbstractController
{   
    private $authorizationChecker;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker
    )
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @return object|null
     * Vérifie que le user est bien connecté et renvoie le user 
     * sinon renvoie vers la page de connexion
     */
    public function checkUser(): ?object
    {
        // on récupère le user connecté
        $user = $this->getUser();
        // on vérifie que le user est bien connecté
        if(!$user) {
            $this->addFlash('warning', 'Vous devez être connecté pour passer une commande');
            return $this->redirectToRoute('app_login');
        }

        if($user && $this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $user;
        }
    }

    /**
     * @Route("/", name="app_user_account")
     */
    public function userAccount(): Response
    {   
        // on récupère le user connecté
        $user = $this->checkUser();
        
        // on récupère les commandes dont la status est new 
        $pending_orders = $user->getOrders()->filter(function($order) {
            return $order->getStatus() === 'new';
        });
        // on récupère les commandes don tle status est 'processing'
        $processing_orders = $user->getOrders()->filter(function($order) {
            return $order->getStatus() === 'processing';
        });

        return $this->render('user/index.html.twig', [
            'pending_orders' => $pending_orders,
            'processing_orders' => $processing_orders,
            'user' => $user,
        ]);
    }
}
