<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UploadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="app_product_index", methods={"GET"})
     */
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findALlVisibleProdcts(),
        ]);
    }

    /**
     * @Route("/new", name="app_product_new", methods={"GET", "POST"})
     */
    public function new(Request $request, ProductRepository $productRepository, EntityManagerInterface $manager, UploadService $uploadService): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($form->get('pictures')->getData() as $i => $picture) {
                // on récupère les fichiers uploadés
                $picture = $uploadService->uploadPictures(
                    // 'product' est le nom du form ProductType (on enlève juste Type au nom) qui est imbriqué et 'pictures' le nom du champ de type collection dans le form parent ProductType
                    $request->files->get('product')['pictures'][$i],
                    $picture,
                    $product
                );
                // on persiste les images au fur et à mesure
                $manager->persist($picture);
            }
            // on ajoute le produit en base de données
            $productRepository->add($product, true);

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_product_show", methods={"GET"})
     */
    public function show(Product $product, ProductRepository $pr): Response
    {   
        
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'relatedProducts' => $pr->relatedProducts($product->getSubCategory()->getId()),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_product_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Product $product, ProductRepository $productRepository, EntityManagerInterface $manager, UploadService $uploadService): Response
    {   
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($form->get('pictures')->getData() as $i => $picture) {
                // on récupère les fichiers uploadés
                $picture = $uploadService->uploadPictures(
                    // 'product' est le nom du formType imbriqué et 'pictures' le nom du champ de type collection dans le form parent
                    $request->files->get('product')['pictures'][$i],
                    $picture,
                    $product
                );
                $manager->persist($picture);
            }

            $productRepository->add($product, true);
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_product_delete", methods={"POST"})
     */
    public function delete(Request $request, Product $product, ProductRepository $productRepository, UploadService $uploadService): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $productRepository->remove($product, true);
        }

        // delete the file from the server if it exists
        foreach ($product->getPictures() as $picture) {
            $uploadService->deletePicture($picture);
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
