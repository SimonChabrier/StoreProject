<?php

namespace App\Controller\Admin;

use App\Entity\User;


use App\Entity\Product;
use App\Entity\Comment;
use App\Entity\ProductType;
use App\Entity\Category;
use App\Entity\SubCategory;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Admin\CategoryCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

// Code source perso 
// https://github.com/SimonChabrier/BackEnd_VideoStreamAndCapture/blob/main/src/Controller/Admin/AdminController.php

class AdminController extends AbstractDashboardController
{   

    /**
     * @Route("/admin", name="app_admin")
     * //@IsGranted("ROLE_ADMIN")
     */
    public function index(): Response
    {
        $routeBuilder = $this->container->get(AdminUrlGenerator::class);
        $url = $routeBuilder->setController(CategoryCrudController::class)->generateUrl();

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
            ->setFaviconPath('favicon.ico')
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
        yield MenuItem::linkToCrud('ProductType.php', 'fa fa-tag', ProductType::class);

        yield MenuItem::section('Admin Commentaires');
        yield MenuItem::linkToCrud('Commentaires', 'fa fa-comment', Comment::class);


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
