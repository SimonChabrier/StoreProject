<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Entity\Product;
use App\Form\ProductType;
use App\Form\AddToCartType;
use App\Manager\CartManager;
use App\Service\UploadService;
use App\Message\UpdateFileMessage;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{   

    const USE_MESSAGE_BUS = false;

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
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, ProductRepository $productRepository, EntityManagerInterface $manager, UploadService $uploadService): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($form->get('pictures')->getData() as $i => $picture) {

                // on récupère les fichiers uploadés
                // 'product' est le nom du formType imbriqué et 'pictures' le nom du champ de type collection dans le form parent
                $alt = $request->request->get('product')['pictures'][$i]['alt'];
                $name = $request->request->get('product')['pictures'][$i]['name'];
                $file = $request->files->get('product')['pictures'][$i];
                
                $picture = $uploadService->processAndUploadPicture($alt, $name, $file, $product);
            
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
     * @Route("/{id}", name="app_product_show")
     */
    public function show(
        Product $product, 
        ProductRepository $pr, 
        Request $request, 
        CartManager $cartManager
    ): Response
    {   
        $form = $this->createForm(AddToCartType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $item = $form->getData();
            $item->setProduct($product);

            $cart = $cartManager->getCurrentCart();
            $cart
                ->addItem($item)
                ->setUpdatedAt(new \DateTime());

            $cartManager->save($cart);

            // add flash message
            $this->addFlash('success', 'Le produit a bien été ajouté au panier');

            // on redirige vers la page produit pour éviter de renvoyer le formulaire en cas de rafraichissement de la page
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'relatedProducts' => $pr->relatedProducts($product->getSubCategory()->getId()),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_product_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(
        Request $request, 
        Product $product, 
        ProductRepository $productRepository, 
        EntityManagerInterface $manager, 
        UploadService $uploadService,
        MessageBusInterface $bus
        ): Response
    {   
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$filesArray = [];
            foreach ($form->get('pictures')->getData() as $i => $picture) {
                // on récupère les fichiers uploadés
                // 'product' est le nom du formType imbriqué et 'pictures' le nom du champ de type collection dans le form parent
                $alt = $request->request->get('product')['pictures'][$i]['alt'];
                $name = $request->request->get('product')['pictures'][$i]['name'];
                $file = $request->files->get('product')['pictures'][$i];
                
                if(!self::USE_MESSAGE_BUS){

                    $picture = $uploadService->processAndUploadPicture($alt, $name, $file, $product);
                    $manager->persist($picture);
                    $productRepository->add($product, true);
                    return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
                
                } else {
                    
                    // on envoie un message à la file d'attente

                    // on récupère le fichier uploadé
                    $file = $request->files->get('product')['pictures'][$i]['file']; 
                    // je donne directement le nom unique au fichier pour éviter de le créer dans le service d'upload
                    $name = $uploadService->setUniqueName();
                    // je déplace le fichier dans le dossier des images originales 
                    $uploadService->moveOriginalFile($file, $name);
                    
                    $bus->dispatch(
                        new UpdateFileMessage(
                            $name,
                            $alt,
                            $product->getId()
                        )
                    );
                }  
                
                return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_product_delete", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Product $product, ProductRepository $productRepository, UploadService $uploadService): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $productRepository->remove($product, true);
        }

        // delete the file from the server if it exists
        foreach ($product->getPictures() as $picture) {
            $uploadService->deletePictures($picture);
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }


}
