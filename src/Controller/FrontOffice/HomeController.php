<?php

namespace App\Controller\FrontOffice;

use App\Service\Notify\EmailService;
use App\Service\Cache\SetProduct;
use App\Service\File\DeleteFileService;
use App\Service\Utils\ConfigurationService;

use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;


use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    private $params;
    private AdapterInterface $cache;
    private ConfigurationService $configurationService;
    private SetProduct $setProduct;
    
    const CACHE_KEY = 'home_data';
    //const CACHE_DURATION = 3600;

    public function __construct(AdapterInterface $cache, ParameterBagInterface $params, ConfigurationService $configurationService, SetProduct $setProduct)
    {   
        $this->cache = $cache;
        $this->params = $params;
        $this->configurationService = $configurationService;
        $this->setProduct = $setProduct;
    }

    /**
     * @Route("/", name="app_home")
     */
    public function index(
        CategoryRepository $categoryRepository, 
        ProductRepository $productRepository
        ): Response
    {   
        dd($productRepository->findAllVisibleProdcts());
        // Récupérer le cache
        $cacheItem = $this->cache->getItem(self::CACHE_KEY);
        $isCacheHit = $cacheItem->isHit();
        $isCacheActive = $this->configurationService->getConfiguration()->isUseCache();

        if(!$isCacheActive) {
            // on vide le cache qu peut déjà exister (non impératif mais plus propre pour maintenir le cache à jour en cas de changement de paramètres)
            $this->cache->deleteItem(self::CACHE_KEY);
            // on récupère les données de la BDD
            $viewData = $categoryRepository->findBy(['showOnHome' => true], ['listOrder' => 'ASC']);
            // on les retourne directement et on sort
            return $this->render('home/index.html.twig', [
                'homeCats' => $viewData,
                'cache' => $isCacheActive,
            ]);
        }

        // Si les données sont en cache on les retourner directement
        if ($cacheItem->isHit()) {
            $viewData = $cacheItem->get();
        // sinon on contruit les données à mettre en cache et on les met en cache
        } else {
            // on récupère les catégories qui ont showOnHome = true
            $categories = $categoryRepository->findBy(['showOnHome' => 'true'], ['listOrder' => 'ASC']);
            
            $dataToCache = [];

                // on boucle sur les catégories
                foreach ($categories as $category) {
                    $categoryData = [
                        'id' => $category->getId(),
                        'name' => $category->getName(),
                        'products' => '',
                        'subCategories' => [],
                    ];
                    // on récupère les produits de chaque catégorie si elle en a uniquement pour ne pas avoir d'erreur dans la vue
                    $categoryProducts = $category->getProducts();
                    if($categoryProducts) {
                        // on stocke le tableau des produits retourné par la méthode setProductData dans la variable $categoryData['products']
                        $categoryData['products'] = $this->setProduct->setProductData($categoryProducts);
                    }
                    // on récupère les sous-catégories de chaque catégorie
                    foreach ($category->getSubCategories() as $subCategory) {
                        $subCategoryData = [
                            'id' => $subCategory->getId(),
                            'name' => $subCategory->getName(),
                            'products' => '',
                        ];
                        // on récupère les produits de chaque sous-catégorie
                        $subCategoryProducts = $productRepository->findBy(['subCategory' => $subCategory->getId(), 'visibility' => 'true'], ['id' => 'DESC'], 4);
                        // si la sous catégorie à des produits
                        if($subCategoryProducts){
                            // on stocke le tableau des produits retourné par la méthode setProductData dans la variable $subCategoryData['products']
                            $subCategoryData['products'] = $this->setProduct->setProductData($subCategoryProducts);
                        }
                        // on stocke les sous-catégories dans le tableau : 'subCategories' => [], de la catégorie
                        $categoryData['subCategories'][] = $subCategoryData;
                    }
                    // on met tout dans le tableau $data
                    $dataToCache[] = $categoryData;
                }

                // on stocke les données dans le cache
                $cacheItem->set($dataToCache)->expiresAfter($this->configurationService->getConfiguration()->getcacheDuration());
                // On sauvegarde le cache
                $this->cache->save($cacheItem);
                // fin du else
            }

            // si les données sont en cache, on les récupère, sinon on les récupère de la BDD
            $viewData = $isCacheHit ? $viewData : $categoryRepository->findBy(['showOnHome' => true], ['listOrder' => 'ASC']);

            // si les données sont en cache, on affiche le template adapté aux tableaux, sinon on affiche le template adpaté aux objets.
            $cache = $isCacheHit ? true : false;
            
            return $this->render('home/index.html.twig', [
                'homeCats' => $viewData,
                'cache' => $cache,
            ]);
    }

    /**
     * @Route("/paginate/{id}", name="app_paginate_products")
     * @IsGranted("ROLE_ADMIN")
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
     * @IsGranted("ROLE_ADMIN")
     */

    public function search(): Response
    {
        return $this->render('_fragments/_searchResults.html.twig', []);
    }

    // test mail route
    /**
     * @Route("/test", name="app_test", methods={"GET", "POST"})
     * @IsGranted("ROLE_ADMIN")
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
                    $this->configurationService->getConfiguration()->getAdminMail(),
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
     * @IsGranted("ROLE_ADMIN")
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

        // Définition du message flash final
        $this->addFlash($type, $message);

        return $this->redirectToRoute('app_home');
    }

    /**
     * @Route("/delete/pictures", name="app_delete_all_pictures")
     * @IsGranted("ROLE_ADMIN")
     */
    public function unlinkAllPictures(DeleteFileService $deleteFileService): Response
    {
        if($deleteFileService->deleteAllPictures()) {
            $this->addFlash('success', 'Toutes les images ont été supprimées.');
        } else {
            $this->addFlash('error', 'Une erreur s\'est produite lors de la suppression des images.');
        }

        return $this->redirectToRoute('app_home');
    }
}
