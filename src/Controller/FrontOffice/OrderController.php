<?php

namespace App\Controller\FrontOffice;

use Stripe\Charge;
use Stripe\Stripe;

use App\Entity\Order;
use App\Form\Order\OrderType;
use App\Repository\OrderRepository;
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
     * Cette route permet d'afficher le panier et son formulaire de modification
     * c'est le formulaire qui contient les produits du panier
     * les boutons d'actions qui dirige ici sont : 
     * "Mettre à jour le panier" (recalcule les quantités si un item est modifié)
     * "Supprimer" (un item du panier)
     * 
     * @Route("/", name="app_order")
     */
    public function index(
        OrderManager $orderManager, 
        Request $request
    ): Response
    {   
        // sur cette page on utilise le panier de la session 
        // si il existe getCurrentCart() le récupère sinon le crée.
        $order = $orderManager->getCurrentCart();
        
        // si on a bien un panier en session avec un id
        if($order->getId()){
            // on récupère le panier de la bdd pour être sur d'avoir les données à jour 
            // et travailler sur le bon panier en cas de modification
            $order = $orderManager->getOrder($order->getId());
        }

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
     * @Route("/checkout/{id}", name="app_order_process")
     */
    public function checkOutOrder(
        Order $order,
        OrderManager $orderManager,
        AuthorizationCheckerInterface $authorizationChecker,
        Request $request
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
            // on récupère la commande en cours
            $order = $orderManager->getOrder($order->getId());
            
            // on dirige vers la page de paiement Stripe
            return $this->redirectToRoute('app_payment_stripe', [
                'id' => $order->getId()
            ]);
        }
    }

    // paiment Stripe form 
    /**
     * @Route("/stripe", name="app_payment_stripe")
     */
    public function stripe(
        Request $request,
        OrderManager $orderManager,
        AuthorizationCheckerInterface $authorizationChecker,
        OrderRepository $orderRepository
    ): Response
    {   
        // on récupère le user connecté
        $user = $this->getUser();

        if(!$user) {
            $this->addFlash('warning', 'Vous devez être connecté pour payer une commande');
            return $this->redirectToRoute('app_login');
        }

        // si on a un user et qu'il est pleinement authentifié
        if($user && $authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
      
            $order = $orderRepository->findOneBy([
                'id' => $request->query->get('id'),
                'user' => $user
            ]);
            
            if(!$order) {
                $this->addFlash('warning', 'Vous n\'avez pas de commande en cours');
                return $this->redirectToRoute('app_order');
            }

            // on place la commande ça va réserver le stock et changer le statut du panier en "processing"
            $orderManager->placeOrder($order, 'paid');
            
            // on dirige vers la page de paiement Stripe
            return $this->render('cart/stripe.html.twig', [
                'order' => $order,
                'stripe_key' => $_ENV["STRIPE_KEY"]
            ]);
        }
    }
    
    /**
 * @Route("/stripe/charge", name="app_stripe_charge", methods={"POST"})
 */
public function createCharge(Request $request): Response
{
    try {
        Stripe::setApiKey($_ENV["STRIPE_SECRET"]);

        // Récupérer le token de la carte depuis le formulaire
        $stripeToken = $request->request->get('stripeToken');

        // Créer la charge
        $charge = Charge::create([
            "amount" => 500, // Montant en centimes (par exemple, 500 centimes = 5€ )
            "currency" => "eur",
            "source" => $stripeToken,
            "description" => "Paiement de commande"
        ]);

        //TODO on décrémente le stock des produits de la commande payée si le paiement est réussi
        

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
     * @Route("/cancel/{id}", name="app_order_cancel")
     */
    public function cancelOrder(Order $order, OrderManager $orderManager) : Response
    {
        $orderManager->cancelOrder($order);

        // retour à la page du profil de l'utilisateur
        return $this->redirectToRoute('app_user_account');
    }
    
}