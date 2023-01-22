<?php

namespace App\Controller;

use App\Entity\AttributeValue;
use App\Form\AttributeValueType;
use App\Repository\AttributeValueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/attribute/value")
 */
class AttributeValueController extends AbstractController
{
    /**
     * @Route("/", name="app_attribute_value_index", methods={"GET"})
     */
    public function index(AttributeValueRepository $attributeValueRepository): Response
    {
        return $this->render('attribute_value/index.html.twig', [
            'attribute_values' => $attributeValueRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_attribute_value_new", methods={"GET", "POST"})
     */
    public function new(Request $request, AttributeValueRepository $attributeValueRepository): Response
    {
        $attributeValue = new AttributeValue();
        $form = $this->createForm(AttributeValueType::class, $attributeValue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $attributeValueRepository->add($attributeValue, true);

            return $this->redirectToRoute('app_attribute_value_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('attribute_value/new.html.twig', [
            'attribute_value' => $attributeValue,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_attribute_value_show", methods={"GET"})
     */
    public function show(AttributeValue $attributeValue): Response
    {
        return $this->render('attribute_value/show.html.twig', [
            'attribute_value' => $attributeValue,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_attribute_value_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, AttributeValue $attributeValue, AttributeValueRepository $attributeValueRepository): Response
    {
        $form = $this->createForm(AttributeValueType::class, $attributeValue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $attributeValueRepository->add($attributeValue, true);

            return $this->redirectToRoute('app_attribute_value_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('attribute_value/edit.html.twig', [
            'attribute_value' => $attributeValue,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_attribute_value_delete", methods={"POST"})
     */
    public function delete(Request $request, AttributeValue $attributeValue, AttributeValueRepository $attributeValueRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$attributeValue->getId(), $request->request->get('_token'))) {
            $attributeValueRepository->remove($attributeValue, true);
        }

        return $this->redirectToRoute('app_attribute_value_index', [], Response::HTTP_SEE_OTHER);
    }
}
