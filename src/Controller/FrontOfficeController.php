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

        if($form->isSubmitted() && $form->isValid()) {
            
            
            $data = $form->getData();
            // valeurs du formulaire
            $min = $data['min'];
            $max = $data['max'];
            $term = $data['search'];

            // valeurs de test si besoin de fixer une valeur par défaut à passer dans les méthodes du repository
            $min1 = 100;
            $max1 = 200;

            // convert $min and max to integer
            $min = (int) $min;
            $max = (int) $max;

            // category repository
            //$results = $cr->findCatsAndSubCatsProductsByPriceMinMax($min, $max);
            $results = $cr->chatGPT2($min, $max);
            //$results = $cr->testOpti($min, $max);
            dump($results);
            // directement sur les produits du repository product c'est la plus rapide car pas de jointure à faire avec les catégories
            dump($pr->findProductsByPriceMinMax($min, $max));
            // product repository
            //$products = $pr->findProductsByPriceMinMax($min, $max);

            $searchResults = $pr->search($term);
            
            // dump('cr' , $results);
            // dump('pr', $products);

            return $this->render('front_office/index.html.twig', [
                'cats' => $results,
                'form' => $form->createView(),
                'min' => $min,
                'max' => $max,
                'searchResults' => $searchResults,
                'searchValue' => $term,
            ]);
        }

        // récupèrer un prix dans la requête GET ou POST envoyé par le formulaire
        // if($price = $request->get('price')) {
        //     $price = (int) $price;
        //     $cats = $cr->findAllVisibleProductsAndCatsAndSubCatsOrderedByListOrder($price);
        // } else {
        //     $price = 0;
        //     $cats = $cr->findAllVisibleProductsAndCatsAndSubCatsOrderedByListOrder($price);
        // }

        // dump($price);

        return $this->render('front_office/index.html.twig', [
            'cats' => $cr->findAllVisibleProductsAndCatsAndSubCatsOrderedByListOrder(),
            'form' => $form->createView(),
        ]);
    }

}
