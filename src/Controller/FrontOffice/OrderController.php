<?php

namespace App\Controller\FrontOffice;

use Stripe\Charge;
use Stripe\Stripe;
use App\Service\Security\CheckUserService;
use App\Entity\Order;
use App\Form\Order\OrderType;
use App\Repository\OrderRepository;
use App\Service\Order\OrderManager;
use App\Service\Order\OrderSessionStorage;
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
            $saveResult = $orderManager->save($order);
    
            if (is_array($saveResult)) {
                // S'il y a des messages d'erreur, on les affiche en tant que messages flash d'erreur
                foreach ($saveResult as $errorMessage) {
                    $this->addFlash('error', $errorMessage);
                }
            } else {
                // Rediriger vers la page du panier
                return $this->redirectToRoute('app_order');
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
    public function checkOutOrder(
        Order $order,
        OrderManager $orderManager
    ): Response
    {   
        // Récupérer l'utilisateur connecté en utilisant le service CheckUserService
        // Vérifie si l'utilisateur est connecté et pleinement authentifié
        $user = $this->checkUserService->getUserIfAuthenticatedFully();

        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté pour passer une commande');
            return $this->redirectToRoute('app_login');
        }

        // Récupérer la commande en cours
        $order = $orderManager->getOrder($order->getId());
        
        // Rediriger vers la page de paiement Stripe
        return $this->redirectToRoute('app_payment_stripe', [
            'id' => $order->getId()
        ]);
}

    /**
     * Traite le paiement via Stripe.
     *
     * @Route("/stripe", name="app_payment_stripe")
     */
    public function stripe(
        Request $request,
        OrderManager $orderManager,
        AuthorizationCheckerInterface $authorizationChecker,
        OrderRepository $orderRepository,
        OrderSessionStorage $orderSessionStorage
    ): Response
    {   
        // Récupérer l'utilisateur connecté
        $user = $this->checkUserService->getUserIfAuthenticatedFully();

        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté pour payer une commande');
            return $this->redirectToRoute('app_login');
        }

        // Récupérer l'ID de la commande depuis la requête
        $orderId = $request->query->get('id');
        
        // Trouver la commande en fonction de l'ID et de l'utilisateur
        $order = $orderRepository->findOneBy([
            'id' => $orderId,
            'user' => $user
        ]);
        
        if (!$order) {
            $this->addFlash('warning', 'Vous n\'avez pas de commande en cours');
            return $this->redirectToRoute('app_order');
        }

        // TODO: Placer la commande (réserver le stock et changer le statut en "processing")
        // $orderManager->payOrder($order, 'paid');

        // TODO: Retirer le panier de la session puisqu'il est payé et présent en BDD.
        // $orderSessionStorage->removeCart();
        
        // Rediriger vers la page de paiement Stripe
        return $this->render('cart/stripe.html.twig', [
            'order' => $order,
            'stripe_key' => $_ENV["STRIPE_KEY"]
        ]);
    }

    
    /**
     * Traite la création de la charge de paiement via Stripe.
     *
     * @Route("/stripe/charge", name="app_stripe_charge", methods={"POST"})
     */
    public function createCharge(Request $request, OrderRepository $orderRepository, OrderManager $orderManager, OrderSessionStorage $orderSessionStorage): Response
    {
        try {
            Stripe::setApiKey($_ENV["STRIPE_SECRET"]);

            // Récupérer le token de la carte depuis le formulaire
            $stripeToken = $request->request->get('stripeToken');
            
            // Récupérer l'ID de la commande depuis le formulaire
            $orderId = $request->request->get('order_id');

            // Trouver la commande en fonction de l'ID et de l'utilisateur
            $order = $orderRepository->findOneBy([
                'id' => $orderId,
                'user' => $this->checkUserService->getUserIfAuthenticatedFully()
            ]);

            // Vérifier que le montant du paiement est correct et correspond au montant de la commande en cours 
            $receivedAmount = $request->request->get('amount');
            $expectedAmount = $order->getTotal();

            if ($receivedAmount != $expectedAmount) {
                throw new \Exception('Montant de paiement incorrect.');
            }

            // Créer la charge
            $charge = Charge::create([
                "amount" => $request->request->get('amount'), // Récupération du montant
                "currency" => "eur",
                "source" => $stripeToken,
                "description" => "Paiement de commande"
            ]);

            // Vérifier si le paiement est réussi
            if ($charge->status !== 'succeeded') {
                throw new \Exception('Le paiement Stripe n\'a pas abouti');
            }

            //* Payer la commande si le paiement est réussi
            $orderManager->payOrder($order, "paid");

            // TODO: Décrémenter le stock des produits de la commande payée si le paiement est réussi
            
            $this->addFlash(
                'success',
                'Paiement réussi !'
            );

        } catch (\Stripe\Exception\CardException $e) {
            $this->addFlash(
                'error',
                $e->getMessage()
            );

            return $this->redirectToRoute('app_payment_stripe', [], Response::HTTP_SEE_OTHER);
        } catch (\Exception $e) {
            $this->addFlash(
                'error',
                'Une erreur s\'est produite lors du paiement.'
            );

            return $this->redirectToRoute('app_payment_stripe', [], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_payment_stripe', [], Response::HTTP_SEE_OTHER);
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
