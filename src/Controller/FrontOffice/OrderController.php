<?php

namespace App\Controller\FrontOffice;

use Stripe\Charge;
use Stripe\Stripe;
use App\Service\Security\CheckUserService;
use App\Entity\Order;
use App\Form\Order\OrderType;
use App\Repository\OrderRepository;
use App\Service\Order\OrderManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @Route("/order")
 */
class OrderController extends AbstractController
{   
    private $checkUserService;

    public function __construct(CheckUserService $checkUserService)
    {
        $this->checkUserService = $checkUserService;
    }

    /**
     * Affiche le panier et son formulaire de modification.
     *
     * @Route("/", name="app_order")
     */
    public function index(
        OrderManager $orderManager,
        Request $request
    ): Response
    {   
        // Récupérer le panier actuel 
        $order = $orderManager->getCurrentCart();
        // Créer le formulaire de modification du panier avec les produits du panier
        $form = $this->createForm(OrderType::class, $order);
        // Gérer les données soumises par le formulaire
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder le panier si des modifications ont été faites
            if($orderManager->save($order)){
                // pas de redirection car on est déjà sur la page du panier on reste sur la même page.
                $this->addFlash('success', 'Le panier a été modifié avec succès');
            } else {
                $this->addFlash('warning', 'Une erreur est survenue lors de la modification du panier');
            }
        }
        // Rendre la page du panier avec le formulaire de modification
        return $this->render('cart/index.html.twig', [
            'cart' => $order,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Traite le processus de validation de la commande.
     *
     * @Route("/checkout/{id}", name="app_order_process")
     */
    public function checkOutOrder(Order $order): Response
    {   
        // Vérifie si l'utilisateur est connecté et pleinement authentifié
        if (!$this->checkUserService->getUserIfAuthenticatedFully()) {
            $this->addFlash('warning', 'Vous devez être connecté pour passer une commande');
            return $this->redirectToRoute('app_login');
        }
        // Rediriger vers la page de paiement Stripe en passant l'ID de la commande en paramètre et en utilisant le code 303 pour rediriger vers une autre page
        return $this->redirectToRoute('app_payment_stripe', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
    }

    /**
     * Traite le paiement via Stripe.
     *
     * @Route("/stripe", name="app_payment_stripe")
     */
    public function stripe(
        Request $request,
        OrderRepository $orderRepository
    ): Response
    {   
        // Vérifie si l'utilisateur est connecté et pleinement authentifié
        if (!$this->checkUserService->getUserIfAuthenticatedFully()) {
            $this->addFlash('warning', 'Vous devez être connecté pour payer une commande');
            return $this->redirectToRoute('app_login');
        }
        // Récupèrer la commande en fonction de l'ID de la commande récupèré depuis la requête dans les paramètres de la route.
        $order = $orderRepository->findOneBy(['id' => $request->query->get('id')]);
        
        if (!$order) {
            $this->addFlash('warning', 'Vous n\'avez pas de commande en cours');
            return $this->redirectToRoute('app_order');
        }        
        // Rediriger vers la page de paiement Stripe 
        // avec les données de la commande pour remplir les champs cachés du formualire de paiement (amout et order_id)
        // et la clé publique de Stripe (stripe_key) utilisé dans le JS de Stripe.
        return $this->render('cart/stripe.html.twig', [
            'order' => $order,
            'stripe_key' => $_ENV["STRIPE_KEY"]
        ]);
    }

    
    /**
     * La soumission du formulaire de paiement Stripe apelle cette route.
     * On récupère les données du formulaire et on crée la charge Stripe.
     *
     * @Route("/stripe/charge", name="app_stripe_charge", methods={"POST"})
     */
    public function createCharge(
        Request $request, 
        OrderRepository $orderRepository, 
        OrderManager $orderManager
        ): Response
    {   
        // Vérifier le jeton CSRF du formulaire de paiement Stripe
        if (!$this->isCsrfTokenValid('paiement', $request->request->get('_token'))) {
            throw new \Exception('Jeton CSRF invalide');
        }

        try {
            // Initialiser Stripe avec la clé secrète
            Stripe::setApiKey($_ENV["STRIPE_SECRET"]);
            // Récupérer le token de la carte depuis le champ caché stripeToken du formulaire qui sera envoyé par le JS de Stripe.
            $stripeToken = $request->request->get('stripeToken');
            // Récupérer l'ID de la commande depuis le formulaire (champ caché)
            $orderId = $request->request->get('order_id');
            // récupèrer l'utilisateur connecté et pleinement authentifié
            $user = $this->checkUserService->getUserIfAuthenticatedFully();
            // Trouver la commande en fonction de l'ID et de l'utilisateur connecté et de l'ID de la commande
            $order = $orderRepository->findOneBy(['id' => $orderId, 'user' => $user]);
            // si je n'ai pas de commande 
            if (!$order) {
                throw new \Exception('Vous n\'avez pas de commande en cours.');
            }
            // Vérifier que le montant du paiement est correct et correspond au montant de la commande en cours
            if ((int) $request->request->get('amount') != (int) $order->getTotal()['totalInCents']) {
                throw new \Exception('Montant de paiement incorrect.');
            }
            // le montant en centiems euros on converti la string en int;
            $amountInCents = (int) $request->request->get('amount'); 
            // Créer la charge stripe
            $charge = Charge::create([
                "amount" => $amountInCents,
                "currency" => "eur",
                "source" => $stripeToken,
                "description" => 'Paiement de commande ' . $order->getId()
            ]);
            // Vérifier si le paiement est réussi ou pas
            if ($charge->status !== 'succeeded') {
                throw new \Exception('Le paiement Stripe n\'a pas abouti');
            }

            //* Payer la commande si le paiement est réussi 
            $orderManager->payOrder($order, "paid");
            $this->addFlash('success','Paiement de : ' . $order->getTotal()['total'] . '€ validé. Merci pour votre commande');
            // on redirige vers la page du profil de l'utilisateur
            return $this->redirectToRoute('app_user_account', [], Response::HTTP_SEE_OTHER);
        
        // on récupère les erreurs retournées par Stripe et on affiche un message d'erreur et on redirige vers la page de paiement Stripe à nouveau.
        } catch (\Stripe\Exception\CardException $e) {
            $this->addFlash('error',$e->getMessage());
            return $this->redirectToRoute('app_payment_stripe', [], Response::HTTP_SEE_OTHER);
        // on récupère aussi les autres erreurs et on affiche un message d'erreur et on redirige vers la page de paiement Stripe à nouveau.
        } 
        catch (\Exception $e) { // ici on récupère les messages d'erreur si ils ne sont pas liés à Stripe et ont été générés par nous avec throw new \Exception.
            $this->addFlash('error','Erreur lors du paiement : ' . $e->getMessage());
            return $this->redirectToRoute('app_payment_stripe', [], Response::HTTP_SEE_OTHER);
        }
    }

    /**
     * Annule une commande.
     *
     * @Route("/cancel/{id}", name="app_order_cancel")
     */
    public function cancelOrder(Order $order, OrderManager $orderManager): Response
    {   
        $user = $this->checkUserService->getUserIfAuthenticatedFully();

        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté pour annuler une commande');
            return $this->redirectToRoute('app_login');
        }

        // Annuler la commande en utilisant le OrderManager
        $orderManager->cancelOrder($order);

        // Rediriger vers la page du profil de l'utilisateur
        return $this->redirectToRoute('app_user_account');
    }
    
}
