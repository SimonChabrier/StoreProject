<?php

namespace App\Controller;

use App\Repository\CategoryRepository;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FrontOfficeController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function index(CategoryRepository $cr): Response
    {
        dump($cr->findAllCatsAndSubCatsOrderedByListOrder());
        return $this->render('front_office/index.html.twig', [
            'cats' => $cr->findAllCatsAndSubCatsOrderedByListOrder(),
        ]);
    }

}
