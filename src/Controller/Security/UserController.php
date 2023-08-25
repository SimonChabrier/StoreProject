<?php

namespace App\Controller\Security;

use App\Service\Security\CheckUserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{   
    /**
     * @Route("/account", name="app_user_account")
     */
    public function showAccount(CheckUserService $checkUserService): Response
    {   
        // on récupère le user connecté ou on renvoie vers la page de connexion
        $user = $checkUserService->getUserIfAuthenticatedFully();

        if(!$user){
            $this->addFlash('warning', 'Vous devez être connecté pour accéder à votre compte.');
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
