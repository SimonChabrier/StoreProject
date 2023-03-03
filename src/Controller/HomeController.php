<?php

namespace App\Controller;

use App\Service\JsonManager;
use App\Service\EmailService;
use App\Message\AdminNotification;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Message\AccountCreatedNotification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{   
    private $adminEmail;

    public function __construct($adminEmail)
    {
        $this->adminEmail = $adminEmail;
    }
    /**
     * @Route("/", name="app_home", methods={"GET", "POST"})
     */
    public function index(CategoryRepository $categoryRepository): Response
    {
    // je récupère la classe de l'alerte qui est définie dans RegistrationController
    // et qui est passée en paramètre dans l'url de la requête avec redirectToRoute
    // qui apelle cette route app_home
    // actuellement non utilisé si j'utilise les flash messages avec SweetAlert2
    //$class = $request->query->get('class', 'alert-success');

        //$this->addFlash('success', 'Message flash de test.');
        
        return $this->render('home/index.html.twig', [
            'homeCats' => $categoryRepository->findBy(['showOnHome' => 'true'], ['listOrder' => 'ASC']),
            //'class' => $class,
        ]);
    
    }

    /**
     * @Route("/paginate/{id}", name="app_paginate_products", methods={"GET", "POST"})
     */
    public function paginateProducts(ProductRepository $pr, Request $request, $id): Response
   {
         // use doctrine query offset and limit to paginate

        // set the number of items per page
        $perPage = 20;
        // set the offset to 0 if page id is 1 
        $offset = ($id - 1) * $perPage;
        // get the total number of items in the database
        $totalPage = count($pr->findAllProductsId());
        // get the total number of pages without float
        $totalPage = ceil($totalPage / $perPage);
        // get the current page
        $currentPage = $id;  
        // get the offset
        $offset = ($currentPage - 1) * $perPage;
        // get the results
        //$results = $pr->findBy([], ['id' => 'ASC'], $perPage, $offset);
        $results = $pr->findPaginateProducts($perPage, $offset);
       
        return $this->render('home/productPagination.html.twig', [
            'products' => $results,
            'pageCount' => $totalPage,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
        ]);
   }

   // search route
   /**
    * @Route("/search", name="app_search", methods={"GET", "POST"})
    */

    public function search(): Response
    {
        return $this->render('_fragments/_jsSearch.html.twig', []);
    }

    // test mail route
    /**
     * @Route("/test", name="app_testmail", methods={"GET", "POST"})
     */
    public function testMail(EmailService $emailService): Response
    {   

        // 3 Définir une route et une action de contrôleur pour déclencher l'envoi 
        // de la notification de création de compte client. 
        // Cette action de contrôleur placera un message messenger 
        // correspondant dans la file d'attente pour être traité par un worker.
        
        $user = $this->getUser();

        try {
            $emailService->sendAccountCreatedNotification($user->getEmail());
            $emailService->sendAdminNotification('Nouveau compte client', $user->getEmail(), 'créé avec succès');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du mail.');
        }
        return $this->redirectToRoute('app_home', []);
    }
}
