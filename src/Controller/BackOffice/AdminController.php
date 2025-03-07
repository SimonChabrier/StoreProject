<?php

namespace App\Controller\BackOffice;

use App\Entity\User;


use App\Entity\Brand;
use App\Entity\Comment;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\ProductType;
use App\Entity\Configuration;

use App\Entity\SubCategory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

// use App\Controller\Admin\CategoryCrudController;
// use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

// Code source perso 
// https://github.com/SimonChabrier/BackEnd_VideoStreamAndCapture/blob/main/src/Controller/Admin/AdminController.php

class AdminController extends AbstractDashboardController
{   
   
    /**
     * @Route("/admin", name="app_admin")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(): Response
    {
        $routeBuilder = $this->container->get(AdminUrlGenerator::class);
        // la page d'accueil de l'admin redirige vers la liste des produits
        $url = $routeBuilder->setController(ProductCrudController::class)->generateUrl();
        
        return $this->redirect($url);

        // je peux aussi retourner un render de template twig ici
        // return $this->render('admin/index.html.twig');

    }


    /**
     * Main Admin Dashboard Title
     * @return Dashboard
     */
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration')
            ->setFaviconPath('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 128 128%22><text y=%221.2em%22 font-size=%2296%22>👟</text></svg>')
            ->renderContentMaximized()
            //->renderSidebarMinimized()
            //->setTranslationDomain('admin')
            //->setTranslationParameters(['%username%' => $this->getUser()->getUsername()])
        ;
    }

    /**
     * Main menu items in left list
     * link here each entity crud we have
     * and/or ad more links
     * https://symfony.com/doc/current/EasyAdminBundle/dashboards.html#menu-item-configuration-options
     * @return iterable
     */
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoRoute('Accueil Site', 'fa fa-home', 'app_home');
        yield MenuItem::section('Admin Utilisateurs');
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user', User::class);

        yield MenuItem::section('Admin Navigation');
        yield MenuItem::linkToCrud('Categories', 'fa fa-bars', Category::class);
        yield MenuItem::linkToCrud('Sous Categories', 'fa fa-indent', SubCategory::class);

        yield MenuItem::section('Admin Produits');
        yield MenuItem::linkToCrud('Produits', 'fa fa-tag', Product::class);
        yield MenuItem::linkToCrud('Types', 'fa fa-tag', ProductType::class);
        yield MenuItem::linkToCrud('Marques', 'fa fa-tag', Brand::class);

        yield MenuItem::section('Admin Commentaires');
        yield MenuItem::linkToCrud('Commentaires', 'fa fa-comment', Comment::class);

        yield MenuItem::section('Admin Configuration');
        yield MenuItem::linkToCrud('Configuration', 'fa fa-cog', Configuration::class);
    }


    /**
     * Personalize Admin Css
     * https://symfony.com/bundles/EasyAdminBundle/current/design.html#customizing-the-backend-design
     */
    // public function configureAssets(): Assets
    // {
    //     return Assets::new()
    //     ->addCssFile('assets/css/admin.css')
    //     ->addHtmlContentToHead('<link rel="shortcut icon" href="favicon.ico">')
    //     ;
    // }

}
