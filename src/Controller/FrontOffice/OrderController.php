<?php

namespace App\Controller\FrontOffice;

use App\Form\Order\OrderType;
use App\Service\Order\OrderManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
        OrderManager $OrderManager, 
        Request $request
    ): Response
    {   

        $order = $OrderManager->getCurrentCart();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $OrderManager->save($order);
            return $this->redirectToRoute('app_order');
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $order,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/process", name="app_order_process")
     */
    public function process(
        OrderManager $OrderManager,
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

            //TODO ici il faudra faire le paiement avec Stripe avant de changer le statut du panier en "processing"

            // on récupère son panier courant
            $cart = $OrderManager->getCurrentCart();
            // on change le statut du panier en "processing"
            $cart->setStatus('processing');
            $cart->setUpdatedAt(new \DateTime());
            $OrderManager->save($cart);

            $this->addFlash('success', 'Votre commande a bien été enregistrée');

            return $this->redirectToRoute('app_order');
        }
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
