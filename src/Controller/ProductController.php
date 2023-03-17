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
    public function new(Request $request, ProductRepository $productRepository): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
        //dump($product);
        
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'relatedProducts' => $pr->relatedProducts($product->getSubCategory()->getId()),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_product_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Product $product, ProductRepository $productRepository, EntityManagerInterface $manager): Response
    {   
        $form = $this->createForm(ProductType::class, $product);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $pictures = $form->get('pictures')->getData();    

            // si j'ai une seule image
            if(count($pictures) === 1)
            {
                //je récupère le nom de l'image unique de la collection
                $name = $pictures->current()->getFileName();
                $picture = $pictures->current()->getFile();
                $ext = $pictures->current()->getFile()->guessExtension();
                // On génère un nouveau nom de fichier
                $file = md5(uniqid()).'.'.$ext;
                
                // On copie le fichier dans le dossier uploads
                $picture->move(
                    $this->getParameter('images_directory'),
                    $file
                );

                // On crée l'image dans la base de données
                $img = new Picture();
                $img->setName($name);
                $img->setFileName($file);
                $img->setProduct($product);
                $manager->persist($img);

                $product->addPicture($img);
                $product->setName($product->getName());

                // fluch the data
                $manager->flush();

                // redirect to the product edit page
                return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);

            }
            // On boucle sur les images
            foreach($pictures as $picture){

                // get the file 
                $name = $picture->getFileName();
                $picture = $picture->getFile();
                //$picture = $picture->getName();
            
                // On génère un nouveau nom de fichier
                $file = md5(uniqid()).'.'.$picture->guessExtension();
                
                // On copie le fichier dans le dossier uploads
                $picture->move(
                    $this->getParameter('images_directory'),
                    $file
                );
                
                // On crée l'image dans la base de données
                $img = new Picture();
                $img->setName($name);
                $img->setFileName($file);
                $img->setProduct($product);
                $manager->persist($img);
        
                $product->addPicture($img);
                $product->setName($product->getName());
            }

            $manager->persist($product);
            $manager->flush();
            //$productRepository->add($product, true);

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
    public function delete(Request $request, Product $product, ProductRepository $productRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $productRepository->remove($product, true);
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
