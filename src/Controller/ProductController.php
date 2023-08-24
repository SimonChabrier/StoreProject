<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\Entity\ProductType;
use App\Form\Order\AddProductToOrderType;
use App\Service\Order\OrderManager;
use App\Service\File\UploadService;
use App\Message\UpdateFileMessage;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{

    const USE_MESSAGE_BUS = true;
    const CACHE_KEY = 'home_data';

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

            // si pas d'image dans le formulaire imbriqué 'pictures' on met à jour le produit sans rien faire d'autre
            if (empty($formPictures)) {
                $this->addFlash('success', 'Le produit a bien été ajouté');
                return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
            }
            // si on a des images dans le formulaire imbriqué 'pictures' on les traite.
            if ($formPictures !== null) {
                self::processFormPictures($formPictures, $request, $product, $uploadService, $bus, $productRepository);
            }

            $this->addFlash('success', 'Produit mis à jour. Images sont en cours de traitement.');
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
        OrderManager $OrderManager
    ): Response {
        $form = $this->createForm(AddProductToOrderType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $item = $form->getData();
            $item->setProduct($product);

            $cart = $OrderManager->getCurrentCart();
            $cart
                ->addItem($item)
                ->setUpdatedAt(new \DateTime());

            $OrderManager->save($cart);

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
        MessageBusInterface $bus,
        ProductRepository $productRepository
    ): Response {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $formPictures = $form->get('pictures')->getData();

            // si pas d'image dans le formulaire imbriqué 'pictures' on met à jour le produit sans rien faire d'autre
            if (empty($formPictures)) {
                // on met à jour le produit avec les données du formulaire
                $productRepository->add($product, true);
                $this->addFlash('success', 'Le produit a bien été mis à jour');
                return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
            }

            // si on a des images dans le formulaire imbriqué 'pictures' on les traite.
            if ($formPictures !== null) {
                self::processFormPictures($formPictures, $request, $product, $uploadService, $bus, $productRepository);  
                $this->addFlash('success', 'Produit mis à jour. Images sont en cours de traitement.');
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
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $productRepository->remove($product, true);
        }

        // delete the file from the server if it exists
        foreach ($product->getPictures() as $picture) {
            $uploadService->deletePicture($picture);
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Gère la logique d'upload des images dans le formulaire imbriqué 'pictures'
     * tout est envoyé à UploadService qui va créer l'image originale en webp.
     * UploadService va ensuite utiliser le ResizerService pour les redimensionner.
     * @param $formPictures
     * @param $request
     * @param $product
     * @param $uploadService
     * @param $bus
     * @return void
     */
    private function processFormPictures($formPictures, $request, $product, $uploadService, $bus, ProductRepository $productRepository)
    {   
        // on met à jour le produit avec les données du formulaire
        $productRepository->add($product, true);

        foreach ($formPictures as $i => $picture) {

            //  on récupère les fichiers uploadés dans le formulaire imbriqué 'pictures'
            // 'product' est le nom du formType de Product
            // 'pictures' le nom du champ de type collection dans le form parent
            // c'est sur cette propriété qu'on imbrique le form PictureType
            $alt = $picture->getAlt();
            $name = $picture->getName();
            $file = $request->files->get('product')['pictures'][$i]['file'];

            if ($file !== null && !self::USE_MESSAGE_BUS) {

                // ici le fichier initial est crée en webp et je récupère son nom pour le trouver das le repertoire et l'associer à l'entité Picture.                
                $newFileName = $uploadService->saveOriginalPictureFile(file_get_contents($file), pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                
                if (!$newFileName) {
                    // runtime exception simple de PHP parce que c'est une erreur qui ne peut pas être anticipée
                    // si le fichier n'est pas créé, on ne peut pas continuer le processus...
                    new \RuntimeException('Erreur lors de la création du fichier');
                } else {
                    $uploadService->createProductPicture(
                        $name,
                        $alt,
                        $newFileName,
                        $product,
                    );
                }
            } else {
                // on utilise Messenger pour traiter les images uploadées en asynchrone
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); // retourne le nom du fichier sans l'extension
                $binaryContent = file_get_contents($file); // retourne le contenu du fichier au format binaire (c'est une string)

                if ($originalName) {
                    $bus->dispatch(
                        new UpdateFileMessage(
                            $name, // la valeur saisi dans le champ name du formulaire imbriqué
                            $alt, // la valeur saisi dans le champ alt du formulaire imbriqué
                            $product->getId(), // l'id du produit
                            $originalName, // le nom du fichier sans l'extension 
                            $binaryContent // le contenu du fichier au format binaire (c'est une string)
                        )
                    )->with(new DelayStamp(30));
                    
                } else {
                    $this->addFlash('danger', 'Une erreur est survenue lors de l\'upload de l\'image');
                }
            }
        }
    }   

}
