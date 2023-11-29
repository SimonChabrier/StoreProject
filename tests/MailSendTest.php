<?php

namespace App\Tests;

use App\Entity\Product;
use Faker\Factory as Faker;
use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use App\Service\File\UploadService;
use App\Service\File\ResizerService;
use App\Service\Notify\EmailService;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class TestMessage
{
    private $subject;

    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    public function getSubject()
    {
        return $this->subject;
    }
}

class MailSendTest extends KernelTestCase
{   

    protected function setUp(): void
    {
        self::bootKernel();
        $this->userRepository = self::$container->get(UserRepository::class);
        $this->adminEmail = self::$container->getParameter('admin_email');
        $this->manager = self::$container->get('doctrine')->getManager();
        $this->picDir = self::$container->getParameter('picDir');
        $this->docDir = self::$container->getParameter('docDir');
        $this->resizer = new ResizerService(...$this->getResizedDirs());
        $this->workflow = self::$container->get('state_machine.picture_publishing');
        $this->registry = self::$container->get(Registry::class);
        $projectDir = self::$kernel->getProjectDir();
        $relativePath = 'public/assets/pictures/';
        $this->absolutePath = $projectDir . '/' . $relativePath;
        $this->uploadService = $this->initUploadService();
    }

    /**
     * Teste l'envoi d'un mail à chaque adresse email de la liste
     * pour tester l'envoie de type newsletter
     * attendu : que la méthode sendTemplateEmailNotification de EmailService
     * soit appelée exactement une fois pour chaque utilisateur disposant d'un compte
     */
    public function testBatchMailSending(): void
    {   
        $subject = 'test_subject';
        $users = $this->userRepository->findAll();
        echo "nombre d'utilisateurs dans la liste : " . count($users) . "\n";
        // Je crée un mock du MessageBusInterface
        $bus = $this->createMock(MessageBusInterface::class);
        // L'attendu est que dispatch soit appelé exactement avec le nombre d'utilisateurs dans la liste
        $bus->expects($this->exactly(count($users)))
            ->method('dispatch')
            ->with($this->anything())
            ->willReturn(new \Symfony\Component\Messenger\Envelope(new TestMessage($subject)));
        echo "nombre d'appels à la méthode dispatch : " . count($users) . "\n";
        // Je crée une instance de EmailService en lui passant le mock du MessageBusInterface
        $emailService = new EmailService($bus);
        // je prépare un tableau pour stocker les résultats
        $results = [];
        // Pour chaque utilisateur dans la liste, j'appelle la méthode  sendTemplateEmailNotification($from, $to, $subject, $template, $context) de EmailService
        foreach ($users as $user) {
            $results[] = $emailService->sendTemplateEmailNotification(
                'from@example.com',
                $user->getEmail(),
                $subject,
                'template_name',
                ['key' => 'value'] // Context
            );
        }
        // est ce que results contient bien autant de résultats que d'utilisateurs dans la liste
        $this->assertEquals(count($users), count($results));
        echo "nombre de mail envoyés : " . count($results) . "\n";
        // Je vérifie que chaque résultat est une instance de Envelope (la classe de base des messages envoyés par Messenger)
        $this->assertContainsOnlyInstancesOf(\Symfony\Component\Messenger\Envelope::class, $results);
        // Je vérifie que chaque message envoyé contient bien le sujet du mail
        foreach ($results as $result) {
            $this->assertEquals($subject, $result->getMessage()->getSubject());
        }
        // jé vérifie que le nombre de résultats est bien égal au nombre d'utilisateurs dans la liste
        $this->assertEquals(count($users), count($results));
       // nombre de résultat et nombre d'utilisateurs dans la liste sont bien égaux
        echo "nombre de messages traités par messenger : " . count($results) . "\n";
        echo "nombre d'utilisateurs dans la liste : " . count($users) . "\n";
    }

    // Teste l'envoi d'un mail à l'admin du site
    // attendu : que la méthode sendAdminNotification($subject, $email, $status) de EmailService soit appelée exactement une fois
    // que le mail de l'admin soit bien récupéré depuis le .env.test depuis service.yaml
    public function testAdminMailSending(): void
    {   
        $subject = 'test_subject';
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->with($this->anything())
            ->willReturn(new \Symfony\Component\Messenger\Envelope(new TestMessage($subject)));
        $emailService = new EmailService($bus);
        $results = [];
        $results[] = $emailService->sendAdminNotification(
            $subject,
            $this->adminEmail,
            'status'
        );
        $this->assertContainsOnlyInstancesOf(\Symfony\Component\Messenger\Envelope::class, $results);
        foreach ($results as $result) {
            $this->assertEquals($subject, $result->getMessage()->getSubject());
        }
        $this->assertEquals(1, count($results));
    }

    // teste l'upload d'une image son redimensionnement et sa création dans la base de données
        public function testImageUpload(): void
        {
        //tester les droits en écriture d'une image sur le dossier $this->picDir
        $uploadService = $this->initUploadService();
        // récupèrer une image de test
        $file = $this->absolutePath . 'defaultSneakersPicture.webp';
        // donner un nom de fichier
        $orignalFileName = 'test';
        // retourne le nom du fichier créé
        $createdFileName = $uploadService->saveOriginalPictureFile(file_get_contents($file), $orignalFileName);
        $this->assertFileExists($this->picDir . '/' . $createdFileName);
        // vérifier que le nom du fichier créé est bien différent du nom du fichier d'origine
        $this->assertNotEquals($orignalFileName, $createdFileName);
       
        echo "le nom du fichier d\'origine est $orignalFileName \n";
        echo "le nom du fichier créé est $createdFileName \n";

        // vérifier le type mime attendu webp
        $mime = $this->assertEquals('image/webp', mime_content_type($this->picDir . '/' . $createdFileName));
        echo "le type mime du fichier créé est " . mime_content_type($this->picDir . '/' . $createdFileName) . "\n";
        
        // récupèrer un produit existant pour tester la création d'une image produit
        $product = $this->manager->getRepository(Product::class)->findOneBy(['name' => 'test']);
        $productId = $product->getId();
        echo "l'id produit récupéré est $productId \n";
        
        // tester a création d'une image produit
        $uploadService->createProductPicture($orignalFileName, 'test', $createdFileName, $product);
        
        // vérifier que le workflow de toutes les images du produit est bien à l'état 'done'
        for($i = 0; $i < count($product->getPictures()); $i++) {
            $state = [];
            $state[] = $product->getPictures()[$i]->getState();
        }
        $this->assertEquals('done', ...$state);
        echo "tous les états des images produits sont bien à l'état 'done' \n";
        
        // vérifier que le fichier créé existe bien dans tous les dossiers de redimensionnement
        $j = 0;
        $dirs = $this->getAllPicturesDirs();
        foreach ($dirs as $dir) {
            $this->assertFileExists($dir . '/' . $createdFileName);
            $dir = substr($dir, strrpos($dir, '/') + 1);
            echo "le fichier $createdFileName a bien été créé dans le dossier $dir \n";
            $j++;
        }
        // vérifier que le nombre de répertoires traités est bien égal au nombre de répertoires de redimensionnement
        $this->assertEquals(7, $j);
        echo "nombre de répertoires traités $j lors de l\'ajout de fichier \n";

        // supprimer le fichier créé
        $i = 0;
        foreach ($dirs as $dir) {
            unlink($dir . '/' . $createdFileName);
            $dir = substr($dir, strrpos($dir, '/') + 1);
            echo "suppression de l'image $i du repertoire $dir \n";
            $i++;
        }

        $pictures = $product->getPictures();
        // vérrifier qu'il y a bien 1 seule image produit qui a été créée
        $this->assertEquals(1, count($pictures));
        echo "nombre d'images produits créées : " . count($pictures) . "\n";
        
        // vérifier que le fichier créé n'existe plus dans tous les dossiers de redimensionnement
        $i = 0;
        foreach ($dirs as $dir) {
            $this->assertFileDoesNotExist($dir . '/' . $createdFileName);
            $dir = substr($dir, strrpos($dir, '/') + 1);
            echo "le fichier $createdFileName a bien été supprimé du dossier $dir \n";
            $i++;
        }
        // vérifier que le fichier créé n'existe plus dans tous les dossiers de redimensionnement
        $this->assertEquals(7, $i);
        echo "nombre de répertoires traités $i lors de la supression de fichier \n";

        // supprimer l'image produit créée dans la base de données
        for($i = 0; $i < count($product->getPictures()); $i++) {
            foreach($product->getPictures() as $picture) {
                $this->manager->remove($picture);
                $this->manager->flush();
            }
        }

        // vérifier que le produit n'a plus d'image associé en base de données
        $this->assertEquals(0, count($product->getPictures()));
        echo "nombre d'images produits après suppression : " . count($product->getPictures()) . "\n";

        // récupèrer l'id du produit à la fin du test
        $finalProductId = $product->getId();
        echo "suppression de $i image produit de la bdd sur le produit id : $finalProductId \n";
        
        // vérifier qu'on traite bien toutjours le même produit que celui récupéré au début du test
        $this->assertEquals($productId, $finalProductId);
        echo "le produit id de départ $productId - product id de fin $finalProductId \n";

    }

    public function getAllPicturesDirs()
    {
        $dirs = [
            self::$container->getParameter('picDir'),
            self::$container->getParameter('pictureXSDir'),
            self::$container->getParameter('picture250Dir'),
            self::$container->getParameter('picture400Dir'),
            self::$container->getParameter('picture800Dir'),
            self::$container->getParameter('picture1200Dir'),
            self::$container->getParameter('slider1280Dir')
        ];
        return $dirs;
    }

    public function getResizedDirs()
    {
        $dirs = [
            self::$container->getParameter('pictureXSDir'),
            self::$container->getParameter('picture250Dir'),
            self::$container->getParameter('picture400Dir'),
            self::$container->getParameter('picture800Dir'),
            self::$container->getParameter('picture1200Dir'),
            self::$container->getParameter('slider1280Dir')
        ];
        return $dirs;
    }

    public function initUploadService(){
        return new UploadService(
            $this->picDir,
            $this->docDir,
            $this->resizer,
            $this->manager,
            $this->registry
        );
    }
   
}

