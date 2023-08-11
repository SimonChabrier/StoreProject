<?php

namespace App\Controller;

use App\Service\EmailService;
use App\Repository\UserRepository;
use App\Repository\PictureRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;

class HomeController extends AbstractController
{   
    private $adminEmail;

    public function __construct($adminEmail)
    {
        $this->adminEmail = $adminEmail;
    }
    /**
     * @Route("/", name="app_home")
     */
    public function index(CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    {
    // je récupère la classe de l'alerte qui est définie dans RegistrationController
    // et qui est passée en paramètre dans l'url de la requête avec redirectToRoute
    // qui apelle cette route app_home
    // actuellement non utilisé si j'utilise les flash messages avec SweetAlert2
    //$class = $request->query->get('class', 'alert-success');

        // $this->addFlash('success', 'SSH.');
        

 

        return $this->render('home/index.html.twig', [
            'homeCats' => $categoryRepository->findBy(['showOnHome' => 'true'], ['listOrder' => 'ASC']),
            //'class' => $class,
        ]);
    }
    /**
     * @Route("/paginate/{id}", name="app_paginate_products")
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
        // TODO retourner du json pour l'ajax
   }

   // TODO search route
   /**
    * @Route("/search", name="app_search", methods={"GET", "POST"})
    */

    public function search(): Response
    {
        return $this->render('_fragments/_searchResults.html.twig', []);
    }

    // test mail route
    /**
     * @Route("/test", name="app_test", methods={"GET", "POST"})
     */
    public function testMail(EmailService $emailService, UserRepository $ur): Response
    {   

        // only admin can access this route
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $user = $this->getUser();
        // find 4 last users
        $users = $ur->findBy([], ['id' => 'DESC'], 4, 0);

        // envoi du mail en bouclant sur les utilisateurs
        foreach ($users as $user) {

            try {
                $emailService->sendTemplateEmailNotification(
                    $this->adminEmail, 
                    $user->getEmail(), 
                    'Nouvelle notification de Sneaker-Shop', 
                    'email/base_email.html.twig', 
                    [   
                        'title' => 'Titre du template depuis le controller',
                        'username' => $user->getEmail(),
                        'subject' => 'Sujet depuis le controller',
                        'content' => 'Message depuis le controller',
                    ],
                );
            } catch (\Exception $e) {
                //dd($e->getMessage());
                $emailService->sendAdminNotification('Erreur à l\'envoi du mail de confirmation de ', $user->getEmail(), 'Message d\'erreur: ' . $e->getMessage());
            }
        }
    
        return $this->redirectToRoute('app_home', []);
    }

    // exemple de route qui exécute une commande shell avec la classe Process de Symfony
    // pour mettre à jour les dépendances Composer du projet
    // cette route est accessible uniquement par les utilisateurs ayant le rôle ROLE_ADMIN

    /**
     * @Route("/composer", name="composer_install")
     */
    public function composerInstall(): Response
    {   
        // only admin can access this route
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // instanciation de la classe Process avec la commande à exécuter
        $process = new Process(['composer', 'install', '--ignore-platform-reqs']);
        $process->setWorkingDirectory('../');
        // Exécution de la commande
        $process->run();

        // Vérification du résultat
        if ($process->isSuccessful()) {
            $message = 'Composer a été mis à jour avec succès.';
            $type = 'success';
        } else {
            $message = 'Une erreur s\'est produite lors de la mise à jour de Composer : ';
            $type = 'error';
        }
        
        //dump($process->getOutput());
        //dump($process->getErrorOutput());
        //dump($message);

        // Définition du message flash final
        $this->addFlash($type, $message);

        return $this->redirectToRoute('app_home');
    }

    /**
     * @Route("/delete/pictures", name="app_product_delete_pictures")
     */
    public function unlinkAllPictures(PictureRepository $pr, Filesystem $filesystem): Response
    {   
        // only admin can access this route
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // TODO ne supprimer que les images qui ne sont pas utilisées par un produit ou une catégorie ou un slider 

        $basePath = '../public/uploads/files/';

        $directories = [
            'pictures',
            'pictures_XS',
            'pictures_250',
            'pictures_400',
            'pictures_1200',
            'slider_1280',
        ];

         // Supprimer les fichiers
        $filesToDelete = [];

        foreach ($directories as $directory) {
            $files = glob($basePath . $directory . '/*');
            // on merge les tableaux pour avoir un seul tableau avec tous les fichiers à supprimer
            // sinon on aurait un tableau par répertoire et il faudrait faire une boucle pour chaque répertoire
            // pour fournir des chemins de fichiers à supprimer à la méthode remove de la classe Filesystem
            $filesToDelete = array_merge($filesToDelete, $files);
        }

        $filesystem->remove($filesToDelete);

        // Supprimer les enregistrements de base de données
        $pictures = $pr->findAll();

        foreach ($pictures as $picture) {
            // true pour que ça flush directement en base de données
            $pr->remove($picture, true);
        }

        $this->addFlash('success', 'Toutes les images ont été supprimées.');
        return $this->redirectToRoute('app_home');
    }
}
