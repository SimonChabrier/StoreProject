<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Entity\Product;
use App\Entity\ProductType;

use App\Entity\User;
use App\Entity\Comment;


use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Faker\Factory as Faker;

class AppFixtures extends Fixture
{   


    private $connexion;

    public function __construct(Connection $connexion)
    {
        $this->connexion = $connexion;
    }

    private function truncate()
    {
        // Unactive foreign key check to make truncate command working
        // TRUNCATE set Auto Increment and Id start at 1
        $this->connexion->executeQuery('SET foreign_key_checks = 0');
        $this->connexion->executeQuery('TRUNCATE TABLE category');
        $this->connexion->executeQuery('TRUNCATE TABLE sub_category');
        $this->connexion->executeQuery('TRUNCATE TABLE product');
        $this->connexion->executeQuery('TRUNCATE TABLE user');
        $this->connexion->executeQuery('TRUNCATE TABLE comment');
        $this->connexion->executeQuery('TRUNCATE TABLE product_type');
    }

    public function load(ObjectManager $manager): void
    {
        $this->truncate();

        $faker = Faker::create('fr_FR');

        $cats = [];

        //create Categories! Bam!
        for ($i = 0; $i < 7; $i++) {
            $category = new Category();
            $names = ['Femme', 'Homme', 'Enfant', 'Equipement', 'Nutrition', 'Soldes', 'Services'];
            $category->setName($names[$i]);
            $category->getName() === 'Homme' ? $category->setListOrder(10) : '';
            $category->getName() === 'Femme' ? $category->setListOrder(20) : '';
            $category->getName() === 'Enfant' ? $category->setListOrder(30) : '';
            $category->getName() === 'Equipement' ? $category->setListOrder(40) : '';
            $category->getName() === 'Nutrition' ? $category->setListOrder(50) : '';
            $category->getName() === 'Soldes' ? $category->setListOrder(60) : '';
            $category->getName() === 'Services' ? $category->setListOrder(70) : '';
            
            // link each subcat to each category
            // $category->addSubCategory($subCats[0]);
            // $category->addSubCategory($subCats[1]);
            // $category->addSubCategory($subCats[2]);
            // $category->addSubCategory($subCats[3]);


            $cats[] = $category;
            
            $manager->persist($category);
        }       

         // Foreach category Femme Homme et Enfant create 4 subcategories! Bam!

         $subCats = [];

        foreach($cats as $cat) {
            if ($cat->getName() === 'Femme' || $cat->getName() === 'Homme' || $cat->getName() === 'Enfant') {
                for ($i = 0; $i < 4; $i++) {
                    $names = ['Vétements', 'Chaussures', 'Accessoires', 'Soldes'];
                    $subCat = new SubCategory();
                    $subCat->setName($names[$i]);
                    $subCat->getName() === 'Vétements' ? $subCat->setListOrder(10) : '';
                    $subCat->getName() === 'Chaussures' ? $subCat->setListOrder(20) : '';
                    $subCat->getName() === 'Accessoires' ? $subCat->setListOrder(30) : '';
                    $subCat->getName() === 'Soldes' ? $subCat->setListOrder(40) : '';

                    $subCats[] = $subCat;
                    
                    $cat->addSubCategory($subCat);
                    $manager->persist($subCat);
                }
            }
        }


        //  $subCats = [];

        //  for ($i = 0; $i < 4; $i++) {
        //      $names = ['Vétements', 'Chaussures', 'Accessoires', 'Soldes'];
        //      $subCat = new SubCategory();
        //      $subCat->setName($names[$i]);
        //      $subCat->getName() === 'Vétements' ? $subCat->setListOrder(10) : '';
        //      $subCat->getName() === 'Chaussures' ? $subCat->setListOrder(20) : '';
        //      $subCat->getName() === 'Accessoires' ? $subCat->setListOrder(30) : '';
        //      $subCat->getName() === 'Soldes' ? $subCat->setListOrder(40) : '';
             
        //      $subCats[] = $subCat;
        //      $manager->persist($subCat);
        //  }

        // link each subcat to each category equipement et nutrition

        // create 3 product type ! Bam!
        $productTypes = [];

        for ($i = 0; $i < 4; $i++) {
            $productType = new ProductType();
            $names = ['jean', 'tea-shirt', 'baskets', 'accessoires musculation'];
            // iterrate over the array to set name in order of array 
            $productType->setName($names[$i]);

            $productTypes[] = $productType;
            $manager->persist($productType);
        }
        
        // create products! Bam!

        $products = [];

        for ($i = 0; $i < 5; $i++) {
            $product = new Product();

            $names = ['Baskets Nike', 'T-shirt Adidas', 'Stop Disque', 'Jean Levis', 'Baskets Puma'];

            $product->setName($names[$i]);
            
            $product->setInStockQuantity(rand(0, 10));
            $instock = $product->getInStockQuantity();
            $instock >= 1 ? $product->setInStock(1) : $product->setInStock(0);
            $instock >= 1 ? $product->setVisibility(1) : $product->setVisibility(0);
            $product->setBuyPrice($faker->numberBetween(80, 1000) * 0.8);
            
            $margin = [1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7];
            $product->setSellingPrice(sprintf('%0.2f',  $product->getbuyPrice() * $margin[rand(0, count($margin) - 1)]));
            $product->setCatalogPrice(sprintf('%0.2f', $product->getSellingPrice() * 1.1));
            
            //SousCats = ['Vétements', 'Chaussures', 'Accessoires', 'Soldes'];
            $product->getName() === 'Baskets Nike' ? $product->setSubCategory($subCats[1]) : '';
            $product->getName() === 'T-shirt Adidas' ? $product->setSubCategory($subCats[0]) : '';
            $product->getName() === 'Stop Disque' ? $product->setSubCategory($subCats[2]) : '';
            $product->getName() === 'Jean Levis' ? $product->setSubCategory($subCats[0]) : '';
            $product->getName() === 'Baskets Puma' ? $product->setSubCategory($subCats[3]) : '';
            

            //types = ['jean', 'tea-shirt', 'baskets', 'accessoires musculation'];
            $product->getName() === 'Baskets Nike' ? $product->setProductType($productTypes[2]) : '';
            $product->getName() === 'T-shirt Adidas' ? $product->setProductType($productTypes[1]) : '';
            $product->getName() === 'Stop Disque' ? $product->setProductType($productTypes[3]) : ''; 
            $product->getName() === 'Jean Levis' ? $product->setProductType($productTypes[0]) : '';
            $product->getName() === 'Baskets Puma' ? $product->setProductType($productTypes[2]) : '';

            // TODO : set productData colection key value mais ça va pas car il faut un {
            //     "1": {
            //         "key": "clé1",
            //         "value": "valeur"
            //     },
            //     "2": {
            //         "key": "clé 2",
            //         "value": "valeur"
            //     }
            // }
            //  et ça fait {"cle":"valeur"} et ça marche pas

            // set productData colection key value
            // $product->getName() === 'T-shirt Adidas' ? $product->setProductData(['taille' => 'L', 'couleur' => 'noir', 'marque' => 'Nike', 'genre' => 'homme', 'matiere' => 'coton']) : '';
            // $product->getName() === 'Baskets Nike' ? $product->setProductData(['taille' => '42', 'couleur' => 'noir', 'marque' => 'Nike', 'genre' => 'homme', 'matiere' => 'cuir']) : '';
            // $product->getName() === 'Jean Levis' ? $product->setProductData(['taille' => 'L', 'couleur' => 'bleu', 'marque' => 'Levis', 'genre' => 'homme', 'matiere' => 'coton']) : '';
            // $product->getName() === 'Stop Disque' ? $product->setProductData(['poids' => '10kg', 'couleur' => 'noir', 'marque' => 'Adidas', 'genre' => 'homme', 'matiere' => 'acier']) : '';
            // $product->getName() === 'Baskets Puma' ? $product->setProductData(['taille' => '42', 'couleur' => 'noir', 'marque' => 'Puma', 'genre' => 'homme', 'matiere' => 'cuir']) : '';
            

            //$product->setVisibility(0);
            
            $products[] = $product;

            $manager->persist($product);
        }

        $user = [];
        // create user with faker ! Bam!
        for ($i = 0; $i < 3; $i++) {
            $user = new User();
            $user->setFirstName($faker->firstName);
            $user->setLastName($faker->lastName);
            $user->setUsername($user->getFirstName(). ' ' .$user->getLastName());
            
            $users[] = $user;

            $manager->persist($user);
        }

        $comments = [];

        // create comments with faker ! Bam!
        for ($i = 0; $i < 10; $i++) {
            $comment = new Comment();
            $comment->setAuthor($users[rand(0, count($users) - 1)]->getFullName());
            $comment->setEmail($faker->email);
            $comment->setText($faker->text(rand(50, 200)));
            $comment->setProduct($products[rand(0, count($products) - 1)]);

            $comments[] = $comment;

            $manager->persist($comment);
        }

        $manager->flush();
    }
}
