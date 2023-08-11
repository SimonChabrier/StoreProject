<?php

namespace App\Controller;

use App\Service\EmailService;
use App\Repository\UserRepository;
use App\Repository\PictureRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{   
    private $adminEmail;
    private $cache;

    public function __construct($adminEmail, AdapterInterface $cache)
    {
        $this->adminEmail = $adminEmail;
        $this->cache = $cache;
    }

    /**
     * @Route("/", name="app_home")
     */
    public function index(
        CategoryRepository $categoryRepository, 
        ProductRepository $productRepository
        ): Response
    {   

        // Récupérer le cache
        $cacheItem = $this->cache->getItem('home_data');
        dump($cacheItem);
        dump($cacheItem->isHit());
        dump($cacheItem->get());
        // Si les données sont en cache, les retourner directement
        if ($cacheItem->isHit()) {
            $data = $cacheItem->get();
        } else {
            // on récupère les catégories qui ont showOnHome = true
            $categories = $categoryRepository->findBy(['showOnHome' => 'true'], ['listOrder' => 'ASC']);
            
            // on rafraîchit le fichier json des produits pour le filtre de recherche
            // $products = $productRepository->findAll();
            // $jsonManager->jsonFileInit($products, 'product:read', 'product.json', 'json');
            // on va stocker les données dans un tableau
            
            $data = [];
            // on boucle sur les catégories
            foreach ($categories as $category) {
                $categoryData = [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'products' => [],
                    'subCategories' => [],
                ];
                // on ajoute les produits si il y a des produits à la racine de la catégorie
                foreach ($category->getProducts() as $product) {
                    $productData = [
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'pictures' => [], // on ne récupère que la première image du tableau : $product->getPictures()[0
                        'catalogPrice' => $product->getCatalogPrice(),
                        'sellingPrice' => $product->getSellingPrice(),
                        'subCategory' => $product->getSubCategory(),
                        'productType' => $product->getProductType()->getName(),
                        'brand' => $product->getBrand()->getName()
                    ];
                    // récupèrer les images du produit
                    foreach ($product->getPictures() as $picture) {
                        // on a besoin du nom du alt et du fileName
                        $productData['pictures'][] = [
                            'id' => $picture->getId(),
                            'alt' => $picture->getAlt(),
                            'fileName' => $picture->getFileName(),
                        ];
                    }
                    // on stocke les produits dans le tableau : 'products' => [], de la catégorie
                    $categoryData['products'][] = $productData;      
                }


                // on récupère tous les produits de la catégorie (pour nous c'est pour la partie "Nouveautés" qui a des produits à sa racine)
                $categoryProducts = $productRepository->findBy(['category' => $category->getId(), 'visibility' => 'true'], ['id' => 'DESC'], 4);
                
                // on boucle sur les produits de la catégorie pour les ajouter dans le tableau : 'products' => [], de la catégorie
                foreach ($categoryProducts as $product) {
                    $productData = [
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'pictures' => [], // on ne récupère que la première image du tableau : $product->getPictures()[0
                        'catalogPrice' => $product->getCatalogPrice(),
                        'sellingPrice' => $product->getSellingPrice(),
                        'subCategory' => $product->getSubCategory(),
                        'productType' => $product->getProductType()->getName(),
                        'brand' => $product->getBrand()->getName()
                    ];
                    // récupèrer les images du produit
                    foreach ($product->getPictures() as $picture) {
                        // on a besoin du nom du alt et du fileName
                        $productData['pictures'][] = [
                            'id' => $picture->getId(),
                            'alt' => $picture->getAlt(),
                            'fileName' => $picture->getFileName(),
                        ];
                    }
                    // on stocke les produits dans le tableau : 'products' => [], de la catégorie
                    $categoryData['products'][] = $productData;      
                }
                // on récupère les sous-catégories de chaque catégorie
                foreach ($category->getSubCategories() as $subCategory) {
                    $subCategoryData = [
                        'id' => $subCategory->getId(),
                        'name' => $subCategory->getName(),
                        'products' => [],
                    ];

                    // on récupère les produits de chaque sous-catégorie
                    $products = $productRepository->findBy(['subCategory' => $subCategory->getId(), 'visibility' => 'true'], ['id' => 'DESC'], 4);
                    // on boucle sur les produits de chaque sous-catégorie pour les ajouter dans le tableau : 'products' => [], de la sous-catégorie
                    foreach ($products as $product) {
                        $productData = [
                            'id' => $product->getId(),
                            'name' => $product->getName(),
                            'pictures' => [],
                            'catalogPrice' => $product->getCatalogPrice(),
                            'sellingPrice' => $product->getSellingPrice(),
                            'subCategory' => $product->getSubCategory(),
                            'productType' => $product->getProductType()->getName(),
                            'brand' => $product->getBrand()->getName(),
                        ];
                        foreach ($product->getPictures() as $picture) {
                            $productData['pictures'][] = [
                                'id' => $picture->getId(),
                                'alt' => $picture->getAlt(),
                                'fileName' => $picture->getFileName(),
                            ];
                        }
                        // on stocke les produits dans le tableau : 'products' => [], de la sous-catégorie
                        $subCategoryData['products'][] = $productData;
                    }
                        // on stocke les sous-catégories dans le tableau : 'subCategories' => [], de la catégorie
                        $categoryData['subCategories'][] = $subCategoryData;
                }
                // on met tout dans le tableau $data
                $data[] = $categoryData;
            }

            // Mettre les données en cache pendant une durée spécifique (par exemple, 1 heure)
            $cacheItem->set($data)->expiresAfter(3600);
            // Enregistrer les données en cache
            $this->cache->save($cacheItem);
            }

            // si les données sont en cache, on les récupère, sinon on les récupère de la BDD
            $cacheItem->isHit() ? $data = $cacheItem->get() : $data = $categoryRepository->findBy(['showOnHome' => 'true'], ['listOrder' => 'ASC']);
            // si les données sont en cache, on affiche le template adapté aux tableaux, sinon on affiche le template adpaté aux objets.
            $cacheItem->isHit() ? $template = 'home/index_cache.html.twig' : $template = 'home/index.html.twig';    

            return $this->render($template, [
                'homeCats' => $data,
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
