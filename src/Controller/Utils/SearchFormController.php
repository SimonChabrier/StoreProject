<?php

namespace App\Controller\Utils;

use App\Service\Utils\SearchFormService;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class SearchFormController extends AbstractController
{
    private $searchFormService;

    public function __construct(SearchFormService $searchFormService)
    {
        $this->searchFormService = $searchFormService;
    }

    // sert à afficher le formulaire de recherche
    public function form(): Response
    {
        $form = $this->searchFormService->createForm();

        return $this->render('_fragments/_searchForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // route ajax déclarée dans le fichier routes.yaml pour processer le formulaire de recherche
    public function submit(Request $request, ProductRepository $pr, CategoryRepository $cr): Response
    {
        $form = $this->searchFormService->createForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $data = $form->getData(); // $data contient les données du formulaire retounées en Ajax par search.js

            $min = (int) $data['min']; // prix min
            $max = (int) $data['max']; // prix max
            $term = $data['search']; // terme de recherche
           
            $cats = $cr->findCatsAndSubCatsProductsByPriceMinMax($min, $max);
            $searchResults = isset($term) ? $pr->search($term) : [];

            return $this->json(
                [
                    'cats' => $cats,
                    'searchResults' => $searchResults,
                ],
                Response::HTTP_OK, 
                [ 'Content-Type' => 'application/json'], 
                ['groups' => 'product:read']
            );

        }

        return $this->json(
            'Pas de résultats',
            Response::HTTP_OK, 
            [ 'Content-Type' => 'application/json'], 
            ['groups' => 'product:read']
        );
    }
}