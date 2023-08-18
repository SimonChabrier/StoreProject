<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Form\AddToCartType;
use App\Manager\CartManager;
use App\Service\UploadService;
use App\Message\UpdateFileMessage;
use App\Repository\ProductRepository;
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
    public function new(
        Request $request,
        ProductRepository $productRepository,
        UploadService $uploadService,
        MessageBusInterface $bus
    ): Response {

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // on ajoute le produit en base de données avec le flag $flush à true pour flusher
            $productRepository->add($product, true);
            // on récupère la valeur du champ pictures du formulaire imbriqué 'pictures' pour évaluer si il y a une image ou pas
            $formPictures = $form->get('pictures')->getData();
            
            if (empty($formPictures)) {
                $this->addFlash('success', 'Le produit a bien été ajouté');
                return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
            }

            if ($formPictures !== null) {
                // on récupère les fichiers uploadés dans le formulaire imbriqué 'pictures'
                foreach ($formPictures as $i => $picture) {

                $alt = $picture->getAlt();
                $name = $picture->getName();
                $file = $request->files->get('product')['pictures'][$i]['file'];
                
                    // si traitement synchrone
                    if ($file !== null && !self::USE_MESSAGE_BUS) {
                        // on utilise le service d'upload pour traiter les images uploadées en synchrone
                        $tempFileName = $uploadService->createTempFile($file);
                        $tempFile = $uploadService->getTempFile($tempFileName);
                        $uploadService->uploadProductPictures($name, $alt, $tempFile, $product);
                        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);

                    } else { // si traitement asynchrone
                        $tempFileName = $uploadService->createTempFile($file);

                        if ($tempFileName) {
                            $bus->dispatch(
                                new UpdateFileMessage(
                                    $name,
                                    $alt,
                                    $product->getId(),
                                    $tempFileName,
                                )
                            );
                        } else {
                            $this->addFlash('danger', 'Une erreur est survenue lors de l\'upload de l\'image');
                        }
                    }
                }  
            }
                $this->addFlash('success', 'Produit ajouté. Images sont en cours de traitement');
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
    ): Response {
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
        UploadService $uploadService,
        MessageBusInterface $bus
    ): Response {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $formPictures = $form->get('pictures')->getData();

            if (empty($formPictures)) {
                $this->addFlash('success', 'Le produit a bien été mis à jour');
                return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
            }

        if ($formPictures !== null) {

            foreach ($formPictures as $i => $picture) {

                //  on récupère les fichiers uploadés dans le formulaire imbriqué 'pictures'
                // 'product' est le nom du formType de Product
                // 'pictures' le nom du champ de type collection dans le form parent
                // c'est sur cette propriété qu'on imbrique le form PictureType
                $alt = $picture->getAlt();
                $name = $picture->getName();
                $file = $request->files->get('product')['pictures'][$i]['file'];

                if ($file !== null && !self::USE_MESSAGE_BUS) {
                    // on utilise le service d'upload pour traiter les images uploadées en synchrone
                    $tempFileName = $uploadService->createTempFile($file);
                    $tempFile = $uploadService->getTempFile($tempFileName);
                    $uploadService->uploadProductPictures($name, $alt, $tempFile, $product);
                    return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);

                } else {
                    // on utilise Messenger pour traiter les images uploadées en asynchrone
                    $tempFileName = $uploadService->createTempFile($file);

                    if ((string)$tempFileName) {
                        $bus->dispatch(
                            new UpdateFileMessage(
                                $name,
                                $alt,
                                $product->getId(),
                                $tempFileName,
                            )
                        );
                    } else {
                        $this->addFlash('danger', 'Une erreur est survenue lors de l\'upload de l\'image');
                    }
                }
            }
        }   
            $this->addFlash('success', 'Produit mis à jour. Images sont en cours de traitement.');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
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
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $productRepository->remove($product, true);
        }

        // delete the file from the server if it exists
        foreach ($product->getPictures() as $picture) {
            $uploadService->deletePictures($picture);
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
