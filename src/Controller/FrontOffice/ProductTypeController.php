<?php

namespace App\Controller\FrontOffice;

use App\Entity\ProductType as Type;
use App\Form\Entity\TypeProductType as TypeProductType;
use App\Repository\ProductTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/product/type")
 * 
 */
class ProductTypeController extends AbstractController
{
    /**
     * @Route("/list", name="app_product_type_index", methods={"GET"})
     */
    public function index(ProductTypeRepository $productTypeRepository): Response
    {   
        return $this->render('product_type/index.html.twig', [
            'product_types' => $productTypeRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="app_product_type_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, ProductTypeRepository $productTypeRepository): Response
    {
        $productType = new Type();
        $form = $this->createForm(TypeProductType::class, $productType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productTypeRepository->add($productType, true);

            return $this->redirectToRoute('app_product_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product_type/new.html.twig', [
            'product_type' => $productType,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_product_type_show", methods={"GET"})
     */
    public function show(Type $productType): Response
    {   
        
        return $this->render('product_type/show.html.twig', [
            'product_type' => $productType,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_product_type_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, Type $productType, ProductTypeRepository $productTypeRepository): Response
    {
        $form = $this->createForm(TypeProductType::class, $productType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productTypeRepository->add($productType, true);

            return $this->redirectToRoute('app_product_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product_type/edit.html.twig', [
            'product_type' => $productType,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_product_type_delete", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Type $productType, ProductTypeRepository $productTypeRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$productType->getId(), $request->request->get('_token'))) {
            $productTypeRepository->remove($productType, true);
        }

        return $this->redirectToRoute('app_product_type_index', [], Response::HTTP_SEE_OTHER);
    }
}
