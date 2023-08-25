<?php

namespace App\Controller\Security;

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
     * @Route("/account", name="app_user_account")
     */
    public function showAccount(): Response
    {   
        // on récupère le user connecté
        $user = $this->checkUser();
        // si on a pas de user on renvoie vers la page de connexion
        if(!$user) {
            return $this->redirectToRoute('app_login');
        }
        // on récupère les commandes dont la status est new 
        $pending_payment_orders = $user->getOrders()->filter(function($order) {
            return $order->getStatus() === 'pending';
        });
        // on récupère les commandes dont le status est 'paid'
        $paid_orders = $user->getOrders()->filter(function($order) {
            return $order->getStatus() === 'paid';
        });
        // on récupère les commandes en cours de préparation
        $preparing_orders = $user->getOrders()->filter(function($order) {
            return $order->getStatus() === 'preparing';
        });
        // on récupère les commandes en cours de livraison
        $shipped_orders = $user->getOrders()->filter(function($order) {
            return $order->getStatus() === 'shipped';
        });
        // on récupère les commandes terminées
        $completed_orders = $user->getOrders()->filter(function($order) {
            return $order->getStatus() === 'completed';
        });
        // on récupère les commandes annulées
        $cancelled_orders = $user->getOrders()->filter(function($order) {
            return $order->getStatus() === 'cancelled';
        });
        
        return $this->render('user/index.html.twig', [
            'pending_payment_orders' => $pending_payment_orders,
            'paid_orders' => $paid_orders,
            'preparing_orders' => $preparing_orders,
            'shipped_orders' => $shipped_orders,
            'completed_orders' => $completed_orders, 
            'cancelled_orders' => $cancelled_orders,
            'user' => $user,
        ]);
    }
}
