<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Form\SearchType;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FrontOfficeController extends AbstractController
{
    /**
     * @Route("/", name="app_home", methods={"GET", "POST"})
     */
    public function index(CategoryRepository $cr, ProductRepository $pr, Request $request): Response
    {   

        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        // reset search
        if($request->request->get('resetSearch') == 'resetSearch') {            
            return $this->render('front_office/index.html.twig', [
                'cats' => $cr->findAllVisibleProductsAndCatsAndSubCatsOrderedByListOrder(),
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
        // return all products    
        return $this->render('front_office/index.html.twig', [
            'cats' => $cr->findAllVisibleProductsAndCatsAndSubCatsOrderedByListOrder(),
            'form' => $form->createView(),
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
