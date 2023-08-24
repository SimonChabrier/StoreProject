<?php

namespace App\Controller\FrontOffice;

use App\Entity\SubCategory;
use App\Form\Entity\SubCategoryType;
use App\Repository\SubCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/sub/category")
 */
class SubCategoryController extends AbstractController
{
    /**
     * @Route("/", name="app_sub_category_index", methods={"GET"})
     */
    public function index(SubCategoryRepository $subCategoryRepository): Response
    {
        return $this->render('sub_category/index.html.twig', [
            'sub_categories' => $subCategoryRepository->getSubCatsOrderByListOrder(),
        ]);
    }

    /**
     * @Route("/new", name="app_sub_category_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, SubCategoryRepository $subCategoryRepository): Response
    {
        $subCategory = new SubCategory();
        $form = $this->createForm(SubCategoryType::class, $subCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $subCategoryRepository->add($subCategory, true);

            return $this->redirectToRoute('app_sub_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('sub_category/new.html.twig', [
            'sub_category' => $subCategory,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_sub_category_show", methods={"GET"})
     */
    public function show(SubCategory $subCategory): Response
    {   
        //dump($subCategory->getProducts());
        return $this->render('sub_category/show.html.twig', [
            'sub_category' => $subCategory,
            'products' => $subCategory->getProducts(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_sub_category_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, SubCategory $subCategory, SubCategoryRepository $subCategoryRepository): Response
    {
        $form = $this->createForm(SubCategoryType::class, $subCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $subCategoryRepository->add($subCategory, true);

            return $this->redirectToRoute('app_sub_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('sub_category/edit.html.twig', [
            'sub_category' => $subCategory,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_sub_category_delete", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, SubCategory $subCategory, SubCategoryRepository $subCategoryRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$subCategory->getId(), $request->request->get('_token'))) {
            $subCategoryRepository->remove($subCategory, true);
        }

        return $this->redirectToRoute('app_sub_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
