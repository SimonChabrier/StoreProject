<?php

namespace App\Controller\FrontOffice;

use App\Entity\Order;
use App\Form\Order\OrderType;
use App\Service\Order\OrderManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


/**
 * @Route("/order")
 */
class OrderController extends AbstractController
{
    /**
     * @Route("/", name="app_order")
     */
    public function index(
        OrderManager $orderManager, 
        Request $request
    ): Response
    {   

        $order = $orderManager->getCurrentCart();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $orderManager->save($order);
            return $this->redirectToRoute('app_order');
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $order,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/process/{id}", name="app_order_process")
     */
    public function placeOrder(
        Order $order,
        OrderManager $orderManager,
        AuthorizationCheckerInterface $authorizationChecker
    ): Response
    {   
        // on récupère le user connecté
        $user = $this->getUser();

        if(!$user) {
            $this->addFlash('warning', 'Vous devez être connecté pour passer une commande');
            return $this->redirectToRoute('app_login');
        }

        // si on a un user et qu'il est pleinement authentifié
        if($user && $authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {

            $order = $orderManager->getCurrentCart();
            // on place la commande ça va réserver le stock et changer le statut du panier en "processing"
            $orderManager->placeOrder($order, 'processing');
            //TODO ici il faudra faire le paiement avec Stripe avant de changer le statut du panier en "processing"

            $this->addFlash('success', 'Votre commande a bien été enregistrée');
            return $this->redirectToRoute('app_user_account');
        }
    }


    /**
     * @Route("/cancel/{id}", name="app_order_cancel")
     */
    public function cancelOrder(Order $order, OrderManager $orderManager) : Response
    {
        $orderManager->cancelOrder($order);

        // retour à la page du profil de l'utilisateur
        return $this->redirectToRoute('app_user_account');
    }

    /**
     * TODO en attente...
     * Affiche le commandes de l'utilisateur connecté
     * @Route("/user", name="app_order_user")
     */
    // public function showUserOrders(
    //     AuthorizationCheckerInterface $authorizationChecker
    // )
    // {
    //     // on récupère le user connecté
    //     $user = $this->getUser();

    //     if(!$user) {
    //         $this->addFlash('warning', 'Vous devez être connecté pour voir vos commandes');
    //         return $this->redirectToRoute('app_login');
    //     }

    //     // si on a un user et qu'il est pleinement authentifié
    //     if($user && $authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {

    //         // on récupère les commandes de l'utilisateur
    //         $orders = $user->getOrders();
    //         dd($orders);
    //         return $this->render('order/user_orders.html.twig', [
    //             'orders' => $orders
    //         ]);
    //     }

    // }
    
    
}
