<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;

use App\Repository\SubCategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FrontOfficeController extends AbstractController
{
    /**
     * @Route("/", name="app_home", methods={"GET", "POST"})
     */
    public function index(CategoryRepository $cr, ProductRepository $pr, SubCategoryRepository $sc, Request $request): Response
    {   
        // dump($pr->findOneBy(['id' => '1']));
        // dump($pr->findAllVisibleProdcts());
        $lastFive = $cr->findAllCatsLastFiveProducts();
        dump($lastFive);
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        //dump($cr->findAll());
        // reset search
        if($request->request->get('resetSearch') == 'resetSearch') {            
            return $this->render('front_office/index.html.twig', [
                'cats' => $cr->findBy([], ['listOrder' => 'ASC']),
                'form' => $form->createView(),
            ]);
        } 
        // return search results
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // (int) convert $min and max to integer
            return $this->render('front_office/index.html.twig', [
                'form' => $form->createView(),
                'min' => $min = (int) $data['min'],
                'max' => $max = (int) $data['max'],
                'searchValue' => $term = $data['search'],
                'searchResults' => isset($term) ? $pr->search($term) : [],
                'cats' => $cr->findCatsAndSubCatsProductsByPriceMinMax($min, $max),
            ]);
        }

        return $this->render('front_office/index.html.twig', [
            // add only categories with products visible
            'cats' => $cr->findBy([], ['listOrder' => 'ASC']),
            'form' => $form->createView(),
            'lastFive' => $lastFive,
        ]);
    }

    /**
     * @Route("/paginate/{id}", name="app_paginate_products", methods={"GET", "POST"})
     */
    public function paginateProducts(ProductRepository $pr, Request $request, $id): Response
   {
         // use doctrine query offset and limit to paginate

        // set the number of items per page
        $perPage = 40;
        // set the offset to 0 if the page is 1 
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
       
        return $this->render('front_office/productPagination.html.twig', [
            'products' => $results,
            'pageCount' => $totalPage,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
        ]);
   }

   /**
     * @Route("/request", name="app_request_products", methods={"GET", "POST"})
     */
   public function requestProducts(CategoryRepository $cr, Request $request): Response
   {
        $results = $cr->findAllCatsAndSubCatsForNavBar();
        dd($results);
        return $this->render('front_office/sql.html.twig', [
            'cats' => $results,
        ]);
   }

}
