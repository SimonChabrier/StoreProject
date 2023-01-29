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
        for ($i = 0; $i < 6; $i++) {
            $category = new Category();
            $names = ['Femme', 'Homme', 'Enfant', 'Equipement', 'Nutrition', 'Soldes'];
            $category->setName($names[$i]);
            $category->getName() === 'Homme' ? $category->setListOrder(10) : '';
            $category->getName() === 'Femme' ? $category->setListOrder(20) : '';
            $category->getName() === 'Enfant' ? $category->setListOrder(30) : '';
            $category->getName() === 'Equipement' ? $category->setListOrder(40) : '';
            $category->getName() === 'Nutrition' ? $category->setListOrder(50) : '';
            $category->getName() === 'Soldes' ? $category->setListOrder(60) : '';
        
            $cats[] = $category;
            
            $manager->persist($category);
        }       

         // Création des sous catégories des catégories Femme, Homme et Enfant
        $subCats = [];
        $nutritionSubCat = [];
        $equipementSubCat = [];


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

            // création des sous catégories de la catégrie Nutrition
            if ($cat->getName() === 'Nutrition') {
                for ($j = 0; $j < 5; $j++) {
                    $namesN = ['Récupération', 'Effort', 'Sèche', 'Masse', 'Soldes'];
                    $subCat = new SubCategory();
                    $subCat->setName($namesN[$j]);
                    $subCat->getName() === 'Récupération' ? $subCat->setListOrder(10) : '';
                    $subCat->getName() === 'Effort' ? $subCat->setListOrder(20) : '';
                    $subCat->getName() === 'Sèche' ? $subCat->setListOrder(30) : '';
                    $subCat->getName() === 'Masse' ? $subCat->setListOrder(40) : '';
                    $subCat->getName() === 'Soldes' ? $subCat->setListOrder(50) : '';

                    $nutritionSubCat[] = $subCat;

                    $cat->addSubCategory($subCat);
                    $manager->persist($subCat);
                }
            }

            // création des sous catégories de la catégorie Equipement
            if ($cat->getName() === 'Equipement') {
                for ($k = 0; $k < 5; $k++) {
                    $namesE = ['Vélo', 'Course', 'Musculation', 'Natation', 'Camping'];
                    $subCat = new SubCategory();
                    $subCat->setName($namesE[$k]);
                    $subCat->getName() === 'Vélo' ? $subCat->setListOrder(10) : '';
                    $subCat->getName() === 'Course' ? $subCat->setListOrder(20) : '';
                    $subCat->getName() === 'Musculation' ? $subCat->setListOrder(30) : '';
                    $subCat->getName() === 'Natation' ? $subCat->setListOrder(40) : '';
                    $subCat->getName() === 'Camping' ? $subCat->setListOrder(50) : '';

                    $equipementSubCat[] = $subCat;

                    $cat->addSubCategory($subCat);
                    $manager->persist($subCat);
                }
            }
           
        }

        // creation des types de produits de la sous catégorie Vélo
        $veloTypes = [];

        for ($i = 0; $i < 3; $i++) {
            $productType = new ProductType();
            $names = ['Vélo de route', 'Vélo de ville', 'Vélo électrique'];
            // iterrate over the array to set name in order of array 
            $productType->setName($names[$i]);

            $veloTypes[] = $productType;
            $manager->persist($productType);
        }

        // creation de 10 vélos avec un random Type de vélo
        $velos = [];

        for ($i = 0; $i < 10; $i++ ){

            $bike = new Product();
            // faker pour le nom du vélo
            $bike->setName('Vélo : ' . $faker->name);
            $bike->setInStockQuantity(rand(1, 10));
            $instock = $bike->getInStockQuantity();
            $instock >= 1 ? $bike->setInStock(1) : $bike->setInStock(0);
            $instock >= 1 ? $bike->setVisibility(1) : $bike->setVisibility(0);
            $bike->setBuyPrice($faker->numberBetween(80, 1000) * 0.8);
            $margin = [1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7];
            $bike->setSellingPrice(sprintf('%0.2f',  $bike->getbuyPrice() * $margin[rand(0, count($margin) - 1)]));
            $bike->setCatalogPrice(sprintf('%0.2f', $bike->getSellingPrice() * 1.1));

            // random bike type from array of bike types ['Vélo de route', 'Vélo de ville', 'Vélo électrique'];
            $bike->setProductType($veloTypes[rand(0, count($veloTypes) - 1)]);

            // TODO lier chaque vélo à une sous catégorie Vélo
            // ['Vélo', 'Course', 'Musculation', 'Natation', 'Camping'];
            $bike->setSubCategory($equipementSubCat[0]);
            // ajouter sous cat vélo à categori equipement 
            // categories ['Femme', 'Homme', 'Enfant', 'Equipement', 'Nutrition', 'Soldes'];
            $bike->setCategory($cats[3]);

            $velos[] = $bike;
            $manager->persist($bike);


        }


        // création de 4 types de produits pour la catégory Vétements
        $vetementTypes = [];

        for ($i = 0; $i < 3; $i++) {
            $productType = new ProductType();
            $names = ['jean', 'tea-shirt', 'baskets'];
            // iterrate over the array to set name in order of array 
            $productType->setName($names[$i]);

            $vetementTypes[] = $productType;
            $manager->persist($productType);
        }
        
        // creation de produits à associer aux sous categories et aux types de produits

        $products = [];

        for ($i = 0; $i < 4; $i++) {
            $product = new Product();

            // gestion du nom du produit
            $names = ['Baskets Nike', 'T-shirt Adidas', 'Jean Levis', 'Baskets Puma'];
            $product->setName($names[$i]);
            
            // gestion de la visibilité et de la quantité en stock pour le moment visibilité = 1 et stock = au moins 1 pour tous les produits
            $product->setInStockQuantity(rand(1, 10));
            $instock = $product->getInStockQuantity();
            $instock >= 1 ? $product->setInStock(1) : $product->setInStock(0);
            $instock >= 1 ? $product->setVisibility(1) : $product->setVisibility(0);
            
            // prix achat et calcul de marge de vente et prix de vente et prix catalogue
            $product->setBuyPrice($faker->numberBetween(80, 1000) * 0.8);
            $margin = [1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7];
            $product->setSellingPrice(sprintf('%0.2f',  $product->getbuyPrice() * $margin[rand(0, count($margin) - 1)]));
            $product->setCatalogPrice(sprintf('%0.2f', $product->getSellingPrice() * 1.1));
            
            //SousCats = ['Vétements', 'Chaussures', 'Accessoires', 'Soldes'];
            $product->getName() === 'Baskets Nike' ? $product->setSubCategory($subCats[1]) : '';
            $product->getName() === 'T-shirt Adidas' ? $product->setSubCategory($subCats[0]) : '';
            $product->getName() === 'Jean Levis' ? $product->setSubCategory($subCats[0]) : '';
            $product->getName() === 'Baskets Puma' ? $product->setSubCategory($subCats[3]) : '';
            

            //types = ['jean', 'tea-shirt', 'baskets', 'accessoires musculation'];
            $product->getName() === 'Baskets Nike' ? $product->setProductType($vetementTypes[2]) : '';
            $product->getName() === 'T-shirt Adidas' ? $product->setProductType($vetementTypes[1]) : '';
            $product->getName() === 'Jean Levis' ? $product->setProductType($vetementTypes[0]) : '';
            $product->getName() === 'Baskets Puma' ? $product->setProductType($vetementTypes[2]) : '';
            
            // set productData colection key value with index in each json entry 
            $product->getName() === 'T-shirt Adidas' ? $product->setProductData(['1' => ['key' => 'taille', 'value' => 'L'], '2' => ['key' => 'couleur', 'value' => 'noir'], '3' => ['key' => 'marque', 'value' => 'Nike'], '4' => ['key' => 'genre', 'value' => 'homme'], '5' => ['key' => 'matiere', 'value' => 'coton']]) : '';
            $product->getName() === 'Baskets Nike' ? $product->setProductData(['1' => ['key' => 'taille', 'value' => '42'], '2' => ['key' => 'couleur', 'value' => 'noir'], '3' => ['key' => 'marque', 'value' => 'Nike'], '4' => ['key' => 'genre', 'value' => 'homme'], '5' => ['key' => 'matiere', 'value' => 'cuir']]) : '';
            $product->getName() === 'Jean Levis' ? $product->setProductData(['1' => ['key' => 'taille', 'value' => 'L'], '2' => ['key' => 'couleur', 'value' => 'bleu'], '3' => ['key' => 'marque', 'value' => 'Levis'], '4' => ['key' => 'genre', 'value' => 'homme'], '5' => ['key' => 'matiere', 'value' => 'coton']]) : '';
            $product->getName() === 'Baskets Puma' ? $product->setProductData(['1' => ['key' => 'taille', 'value' => '42'], '2' => ['key' => 'couleur', 'value' => 'noir'], '3' => ['key' => 'marque', 'value' => 'Puma'], '4' => ['key' => 'genre', 'value' => 'homme'], '5' => ['key' => 'matiere', 'value' => 'cuir']]) : '';
            
            // CATS = ['Femme', 'Homme', 'Enfant', 'Equipement', 'Nutrition', 'Soldes'];
            //Add category to product by name 
            $product->getName() === 'Baskets Nike' ? $product->setCategory($cats[1]) : '';
            $product->getName() === 'T-shirt Adidas' ? $product->setCategory($cats[2]) : '';
            $product->getName() === 'Jean Levis' ? $product->setCategory($cats[1]) : '';
            $product->getName() === 'Baskets Puma' ? $product->setCategory($cats[5]) : '';
            
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


