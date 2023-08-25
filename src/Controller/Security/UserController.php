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
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // pour stocker les commandes selon leur statut
        $ordersByStatus = [
            'new' => [], 
            'pending' => [],
            'paid' => [],
            'preparing' => [],
            'shipped' => [],
            'completed' => [],
            'cancelled' => [],
        ];

        // Parcourir les commandes de l'utilisateur et les trier par statut
        foreach ($user->getOrders() as $order) {
            $status = $order->getStatus();
            if (array_key_exists($status, $ordersByStatus)) {
                $ordersByStatus[$status][] = $order;
            }
        }

        return $this->render('user/index.html.twig', [
            'ordersByStatus' => $ordersByStatus,
            'user' => $user,
        ]);
    }
}
