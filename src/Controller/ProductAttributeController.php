<?php

namespace App\Controller;

use App\Entity\ProductAttribute;
use App\Form\ProductAttributeType;
use App\Repository\ProductAttributeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/product/attribute")
 */
class ProductAttributeController extends AbstractController
{
    /**
     * @Route("/", name="app_product_attribute_index", methods={"GET"})
     */
    public function index(ProductAttributeRepository $productAttributeRepository): Response
    {
        return $this->render('product_attribute/index.html.twig', [
            'product_attributes' => $productAttributeRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_product_attribute_new", methods={"GET", "POST"})
     */
    public function new(Request $request, ProductAttributeRepository $productAttributeRepository): Response
    {
        $productAttribute = new ProductAttribute();
        $form = $this->createForm(ProductAttributeType::class, $productAttribute);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productAttributeRepository->add($productAttribute, true);

            return $this->redirectToRoute('app_product_attribute_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product_attribute/new.html.twig', [
            'product_attribute' => $productAttribute,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_product_attribute_show", methods={"GET"})
     */
    public function show(ProductAttribute $productAttribute): Response
    {
        return $this->render('product_attribute/show.html.twig', [
            'product_attribute' => $productAttribute,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_product_attribute_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, ProductAttribute $productAttribute, ProductAttributeRepository $productAttributeRepository): Response
    {
        $form = $this->createForm(ProductAttributeType::class, $productAttribute);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productAttributeRepository->add($productAttribute, true);

            return $this->redirectToRoute('app_product_attribute_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product_attribute/edit.html.twig', [
            'product_attribute' => $productAttribute,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_product_attribute_delete", methods={"POST"})
     */
    public function delete(Request $request, ProductAttribute $productAttribute, ProductAttributeRepository $productAttributeRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$productAttribute->getId(), $request->request->get('_token'))) {
            $productAttributeRepository->remove($productAttribute, true);
        }

        return $this->redirectToRoute('app_product_attribute_index', [], Response::HTTP_SEE_OTHER);
    }
}
