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



class FileUploadTest extends KernelTestCase
{   

    protected function setUp(): void
    {
        self::bootKernel();

        $this->manager = self::$container->get('doctrine')->getManager();
        $this->workflow = self::$container->get('state_machine.picture_publishing');
        $this->registry = self::$container->get(Registry::class);
        $this->picDir = self::$container->getParameter('picDir');
        $this->docDir = self::$container->getParameter('docDir');
        $this->resizer = new ResizerService(...$this->getResizedDirs());
        $this->uploadService = $this->initUploadService();
        $this->dirs = $this->getAllPicturesDirs();
        $this->testPictureFilePath = self::$kernel->getProjectDir() . '/public/assets/pictures/defaultSneakersPicture.webp';  

    }


    /**
     * Teste l'upload d'un fichier
     * le redimentionnement de l'image dans tous les dossiers de redimensionnement
     * la création d'une image produit
     *
     * @return void
     */
    public function testImageUpload(): void
    {
        //tester les droits en écriture d'une image sur le dossier $this->picDir
        $uploadService = $this->initUploadService();
        // donner un nom de fichier d'origine (ce qui serait récupére sur name dans le formulaire)
        $originalFileName = 'test';
        // retourne le nom du fichier créé
        $createdFileName = $uploadService->saveOriginalPictureFile(file_get_contents($this->testPictureFilePath), $originalFileName);
        // vérifier que le fichier créé existe bien dans le dossier $this->picDir le dossier de base
        $this->assertFileExists($this->picDir . '/' . $createdFileName);
        echo "le fichier $createdFileName existe bien dans le dossier $this->picDir \n";
        // attendu que le nom du fichier créé soit différent du nom du fichier d'origine
        $this->assertNotEquals($originalFileName, $createdFileName);
        echo "le nom du fichier d\'origine est $originalFileName et le nom du fichier crée est $createdFileName \n";
        // attendu que le fichier créé soit bien un fichier webp
        $mime = $this->assertEquals('image/webp', mime_content_type($this->picDir . '/' . $createdFileName));
        echo "le type mime du fichier créé est " . mime_content_type($this->picDir . '/' . $createdFileName) . "\n";
        
        // récupèrer un produit existant pour tester la création d'une image produit
        $product = $this->manager->getRepository(Product::class)->findOneBy(['name' => 'test']);
        $productId = $product->getId();
        echo "l'id produit récupéré est $productId \n";
    
        // attendu ajout de l'image produit dans la base de données et redimensionnement dans tous les dossiers de redimensionnement
        $uploadService->createProductPicture($originalFileName, 'contenu de la balise alt', $createdFileName, $product);
        // attendu que le workflow soit bien passé à l'état 'done' pour l'image produit créée
        $this->assertEquals('done', $product->getPictures()[0]->getState());
        echo "l'état de l'image produit créée est " . $product->getPictures()[0]->getState() . "\n";
        
        // attendu que le fichier créé existe bien dans tous les dossiers de redimensionnement
        $j = 0;
        foreach ($this->dirs as $dir) {
            $this->assertFileExists($dir . '/' . $createdFileName);
            $dir = substr($dir, strrpos($dir, '/') + 1);
            echo "le fichier $createdFileName a bien été créé dans le dossier $dir \n";
            $j++;
        }
        // attandu que l'on traite bien tous les dossiers de redimensionnement
        $this->assertEquals(7, $j);
        echo "$j répertoires traités \n";

        // attendu que le fichier créé existe bien dans le dossier de base en plus des dossiers de redimensionnement
        $picture = $this->picDir . '/' . $createdFileName;
        $this->assertFileExists($picture);
        echo "le fichier $createdFileName existe bien dans le dossier $this->picDir \n";

        // attendu que le produit ait bien une seule image associée en base de données
        $this->assertEquals(1, count($product->getPictures()));
        echo "nombre d'images produits créées : " . count($product->getPictures()) . "\n";
        
        // attendu que le méthode deleteAllpictures supprime bien tous les fichiers créés dans tous les dossiers de redimensionnement
        $uploadService->deleteAllpictures($this->dirs, $createdFileName);
        $i = 0;
        foreach ($this->dirs as $dir) {
            $this->assertFileDoesNotExist($dir . '/' . $createdFileName);
            $dir = substr($dir, strrpos($dir, '/') + 1);
            echo "le fichier $createdFileName a bien été supprimé du dossier $dir \n";
            $i++;
        }
        // attendu que l'on traite bien tous les dossiers de redimensionnement lors de la supression de fichier
        $this->assertEquals(7, $i);
        echo "nombre de répertoires traités $i lors de la supression de fichier \n";

        // attendu que le fichier soit bien supprimé de la base de données
        $this->manager->remove($product->getPictures()[0]);
        $this->manager->flush();
        $this->assertEquals(0, count($product->getPictures()));
        echo "nombre d'images produits après suppression : " . count($product->getPictures()) . "\n";

        // attendu vérifier qu'on traite bien toutjours le même produit que celui récupéré au début du test
        $finalProductId = $product->getId();
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

