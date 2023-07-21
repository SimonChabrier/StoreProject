<?php

namespace App\Controller;

use App\Form\CartType;
use App\Service\Cart\CartManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class CartController
 * @package App\Controller
 */
class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="app_cart")
     */
    public function index(CartManager $cartManager, Request $request): Response
    {   
        // on récupère le panier courant :
        // soit il existe en session et on le récupère, soit on le crée et on le récupère
        // le tout depuis le service CartManager
        $cart = $cartManager->getCurrentCart();

        // dump de la session pour voir le panier
        // même si j'ajoute des produits sans être connecté, le panier est bien conservé en session
        // si je me connecte, le panier est bien conservé en BDD et en session avec son id 
        // je peux donc ajouter des produits sans être connecté et les retrouver dans mon panier une fois connecté
        // dump($request->getSession()->get('cart_id'));
        
        $form = $this->createForm(CartType::class, $cart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $cart->setUpdatedAt(new \DateTime());
            // on sauvegarde le panier en BDD et en session
            $cartManager->save($cart);
            
            return $this->redirectToRoute('app_cart');
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'form' => $form->createView()
        ]);
    }
}