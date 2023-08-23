<?php

namespace App\Controller;

use App\Form\CartType;
use App\Manager\CartManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


class OrderController extends AbstractController
{
    /**
     * @Route("/order", name="app_order")
     */
    public function index(
        CartManager $cartManager, 
        Request $request
    ): Response
    {   

        $cart = $cartManager->getCurrentCart();
        $form = $this->createForm(CartType::class, $cart);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cart->setUpdatedAt(new \DateTime());
            $cartManager->save($cart);

            return $this->redirectToRoute('app_order');
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/order/process", name="app_order_process")
     */
    public function process(
        CartManager $cartManager,
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
            $cart = $cartManager->getCurrentCart();
            // on change le statut du panier en "processing"
            $cart->setStatus('processing');
            $cart->setUpdatedAt(new \DateTime());
            $cartManager->save($cart);

            $this->addFlash('success', 'Votre commande a bien été enregistrée');

            return $this->redirectToRoute('app_order');
        }
        
    }
}
