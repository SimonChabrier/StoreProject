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

            $productType->addSubCategory($equipementSubCat[0]);

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

        // creation de types de produit de la sous catégorie Recupération
        $recuperationTypes = [];

        for ($i = 0; $i < 3; $i++) {
            $productType = new ProductType();
            $names = ['Protéine', 'Glucides', 'Electrolytes'];
            // iterrate over the array to set name in order of array 
            $productType->setName($names[$i]);
            
            $productType->addSubCategory($nutritionSubCat[rand(0, count($nutritionSubCat) - 1)]);

            $recuperationTypes[] = $productType;
            $manager->persist($productType);
        }

        // creation de 10 produits de la sous catégorie Recupération
        $recuperations = [];

        for ($i = 0; $i < 10; $i++) {
            $recuperation = new Product();
            // faker pour le nom du produit
            $recuperation->setName($faker->word());
            $recuperation->setInStockQuantity(rand(1, 10));
            $instock = $recuperation->getInStockQuantity();
            $instock >= 1 ? $recuperation->setInStock(1) : $recuperation->setInStock(0);
            $instock >= 1 ? $recuperation->setVisibility(1) : $recuperation->setVisibility(0);
            $recuperation->setBuyPrice($faker->numberBetween(80, 1000) * 0.8);
            $margin = [1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7];
            $recuperation->setSellingPrice(sprintf('%0.2f', $recuperation->getbuyPrice() * $margin[rand(0, count($margin) - 1)]));
            $recuperation->setCatalogPrice(sprintf('%0.2f', $recuperation->getSellingPrice() * 1.1));

            // random bike type from array of bike types ['Vélo de route', 'Vélo de ville', 'Vélo électrique'];
            $recuperation->setProductType($recuperationTypes[rand(0, count($recuperationTypes) - 1)]);

            // TODO lier chaque vélo à une sous catégorie Vélo
            // ['Vélo', 'Course', 'Musculation', 'Natation', 'Camping'];
            $recuperation->setSubCategory($nutritionSubCat[0]);
            // ajouter sous cat vélo à categori equipement
            // categories ['Femme', 'Homme', 'Enfant', 'Equipement', 'Nutrition', 'Soldes'];
            $recuperation->setCategory($cats[4]);
            $recuperation->setProductType($recuperationTypes[rand(0, count($recuperationTypes) - 1)]);

            $recuperations[] = $recuperation;
            $manager->persist($recuperation);
        }

        // création de 4 types de produits pour la catégory Vétements
        $vetementTypes = [];

        for ($i = 0; $i < 3; $i++) {
            $productType = new ProductType();
            $names = ['jean', 'tea-shirt', 'baskets'];
            // iterrate over the array to set name in order of array 
            $productType->setName($names[$i]);

            //TODO lier chaque type de produit à une sous catégorie

            $vetementTypes[] = $productType;
            $manager->persist($productType);
        }

        // creation de types de produit pour la Musculation
        $musculationTypes = [];
        
        for ($i = 0; $i < 3; $i++) {
            $productType = new ProductType();
            $names = ['Tapis de sol', 'Barre', 'Haltères'];
            // iterrate over the array to set name in order of array 
            $productType->setName($names[$i]);

            $productType->addSubCategory($equipementSubCat[rand(0, count($equipementSubCat) - 1)]);

            $musculationTypes[] = $productType;
            $manager->persist($productType);
        }

        // creation de produits pour pour le type Musculation
        $musculations = [];

        for ($i = 0; $i < 10; $i++) {
            $musculation = new Product();
            // faker pour le nom du produit
            $musculation->setName($faker->word());
            $musculation->setInStockQuantity(rand(1, 10));
            $instock = $musculation->getInStockQuantity();
            $instock >= 1 ? $musculation->setInStock(1) : $musculation->setInStock(0);
            $instock >= 1 ? $musculation->setVisibility(1) : $musculation->setVisibility(0);
            $musculation->setBuyPrice($faker->numberBetween(80, 1000) * 0.8);
            $margin = [1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7];
            $musculation->setSellingPrice(sprintf('%0.2f', $musculation->getbuyPrice() * $margin[rand(0, count($margin) - 1)]));
            $musculation->setCatalogPrice(sprintf('%0.2f', $musculation->getSellingPrice() * 1.1));

            // random bike type from array of bike types ['Vélo de route', 'Vélo de ville', 'Vélo électrique'];
            $musculation->setProductType($musculationTypes[rand(0, count($musculationTypes) - 1)]);

            // TODO lier chaque vélo à une sous catégorie Vélo
            // ['Vélo', 'Course', 'Musculation', 'Natation', 'Camping'];
            $musculation->setSubCategory($equipementSubCat[2]);
            // ajouter sous cat vélo à categori equipement 
            // categories ['Femme', 'Homme', 'Enfant', 'Equipement', 'Nutrition', 'Soldes'];
            $musculation->setCategory($cats[3]);

            $musculation->setProductType($musculationTypes[rand(0, count($musculationTypes) - 1)]);

            $musculations[] = $musculation;
            $manager->persist($musculation);
        }

        // creation de types de produit pour la Natation
        $natationTypes = [];

        for ($i = 0; $i < 3; $i++) {
            $productType = new ProductType();
            $names = ['Maillot', 'Maillot de bain', 'Maillot de corps'];
            // iterrate over the array to set name in order of array 
            $productType->setName($names[$i]);

            // link type to subcategory
            $productType->addSubCategory($equipementSubCat[rand(0, count($equipementSubCat) - 1)]);

            $natationTypes[] = $productType;
            $manager->persist($productType);
        }

        // creation de produits pour pour le type Natation
        $natations = [];

        for ($i = 0; $i < 10; $i++) {
            $natation = new Product();
            // faker pour le nom du produit
            $natation->setName($faker->word());
            $natation->setInStockQuantity(rand(1, 10));
            $instock = $natation->getInStockQuantity();
            $instock >= 1 ? $natation->setInStock(1) : $natation->setInStock(0);
            $instock >= 1 ? $natation->setVisibility(1) : $natation->setVisibility(0);
            $natation->setBuyPrice($faker->numberBetween(80, 1000) * 0.8);
            $margin = [1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7];
            $natation->setSellingPrice(sprintf('%0.2f', $natation->getbuyPrice() * $margin[rand(0, count($margin) - 1)]));
            $natation->setCatalogPrice(sprintf('%0.2f', $natation->getSellingPrice() * 1.1));

            // random bike type from array of bike types ['Vélo de route', 'Vélo de ville', 'Vélo électrique'];
            $natation->setProductType($natationTypes[rand(0, count($natationTypes) - 1)]);

            // TODO lier chaque vélo à une sous catégorie Vélo
            // ['Vélo', 'Course', 'Musculation', 'Natation', 'Camping'];
            $natation->setSubCategory($equipementSubCat[3]);
            // ajouter sous cat vélo à categori equipement
            // categories ['Femme', 'Homme', 'Enfant', 'Equipement', 'Nutrition', 'Soldes'];
            $natation->setCategory($cats[3]);
            $natation->setProductType($natationTypes[rand(0, count($natationTypes) - 1)]);

            $natations[] = $natation;
            $manager->persist($natation);
        }

        // creation de types de produit pour la Camping

        $campingTypes = [];

        for ($i = 0; $i < 3; $i++) {
            $productType = new ProductType();
            $names = ['Tente', 'Sac de couchage', 'Matelas'];
            // iterrate over the array to set name in order of array 
            $productType->setName($names[$i]);

            $campingTypes[] = $productType;
            $manager->persist($productType);
        }

        // création de 10 produits pour le type camping
        $campings = [];

        for ($i = 0; $i < 10; $i++) {
            $camping = new Product();
            // faker pour le nom du produit
            $camping->setName($faker->word());
            $camping->setInStockQuantity(rand(1, 10));
            $instock = $camping->getInStockQuantity();
            $instock >= 1 ? $camping->setInStock(1) : $camping->setInStock(0);
            $instock >= 1 ? $camping->setVisibility(1) : $camping->setVisibility(0);
            $camping->setBuyPrice($faker->numberBetween(80, 1000) * 0.8);
            $margin = [1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7];
            $camping->setSellingPrice(sprintf('%0.2f', $camping->getbuyPrice() * $margin[rand(0, count($margin) - 1)]));
            $camping->setCatalogPrice(sprintf('%0.2f', $camping->getSellingPrice() * 1.1));

            // random bike type from array of bike types ['Vélo de route', 'Vélo de ville', 'Vélo électrique'];
            $camping->setProductType($campingTypes[rand(0, count($campingTypes) - 1)]);

            // TODO lier chaque produit à une sous catégorie
            // ['Vélo', 'Course', 'Musculation', 'Natation', 'Camping'];
            $camping->setSubCategory($equipementSubCat[4]);
            // ajouter chaque produit à une categorie
            $camping->setCategory($cats[3]);
            // ajouter un type de produit à chaque produit
            $camping->setProductType($campingTypes[rand(0, count($campingTypes) - 1)]);

            $campings[] = $camping;
            $manager->persist($camping);
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


