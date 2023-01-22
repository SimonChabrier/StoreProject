<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Comment;
use App\Entity\ProductType;
use App\Entity\ProductAttribute;

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
        $this->connexion->executeQuery('TRUNCATE TABLE product_attribute');
    }

    public function load(ObjectManager $manager): void
    {
        $this->truncate();

        $faker = Faker::create('fr_FR');

        $subCats = [];
        
        // create 20 Subcat! Bam!
        for ($i = 0; $i < 5; $i++) {
            $subCat = new SubCategory();
            $subCat->setName('SubCat '.($i +1));
            $subCat->setListOrder(rand(0, 9999));
            $subCats[] = $subCat;
            $manager->persist($subCat);
        }

        $cats = [];

        //create 4 categories! Bam!
        for ($i = 0; $i < 4; $i++) {
            $category = new Category();
            $category->setName('category '.($i +1));
            $category->setListOrder(rand(0, 9999));
            // liens vers les sous catégories
            for ($j = 0; $j < 5; $j++) {
                $category->addSubCategory($subCats[rand(0, count($subCats) - 1)]);
            }
            $cats[] = $category;
            
            $manager->persist($category);
        }       

        // create 3 product type ! Bam!
        $productTypes = [];
        $productAttributes = [];

        for ($i = 0; $i < 3; $i++) {
            $productType = new ProductType();
            $names = ['jean', 'tea-shirt', 'baskets'];
            // iterrate over the array to set name in order of array 
            $productType->setName($names[$i]);

            $productTypes[] = $productType;
            $manager->persist($productType);
        }

        // create 3 product attributes! Bam!
        for ($i = 0; $i < 3; $i++) {
            $productAttribute = new ProductAttribute();

            $names = ['pointure', 'couleur', 'taille'];
            $productAttribute->setName($names[$i]);

            $type = ['text', 'number'];
            // set text if != couleur
            if($names[$i] != 'couleur'){
                $productAttribute->setType($type[0]);
            }else{
                $productAttribute->setType($type[1]);
            }

            $productAttributes[] = $productAttribute;
            $productAttribute->addProductType($productTypes[rand(0, count($productTypes) - 1)]);
            $manager->persist($productAttribute);
        }

        foreach($productTypes as $productType){
            $productType->addAttribute($productAttributes[rand(0, count($productAttributes) - 1)]);
            $manager->persist($productType);
        }
        
        // create 20 products! Bam!

        $products = [];

        for ($i = 0; $i < 100; $i++) {
            $product = new Product();
            $product->setName('product '.($i +1));
            
            $product->setInStockQuantity(rand(0, 10));
            $instock = $product->getInStockQuantity();
            $instock >= 1 ? $product->setInStock(1) : $product->setInStock(0);
            $instock >= 1 ? $product->setVisibility(1) : $product->setVisibility(0);
            $product->setBuyPrice($faker->numberBetween(80, 1000) * 0.8);
            $margin = [1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7];
            $product->setSellingPrice(sprintf('%0.2f',  $product->getbuyPrice() * $margin[rand(0, count($margin) - 1)]));
            $product->setCatalogPrice(sprintf('%0.2f', $product->getSellingPrice() * 1.1));
            $product->setCategory($cats[rand(0, count($cats) - 1)]);
            $product->setSubCategory($subCats[rand(0, count($subCats) - 1)]);
            $product->setProductType($productTypes[rand(0, count($productTypes) - 1)]);
            
            //$product->setVisibility(0);
            
            $products[] = $product;

            $manager->persist($product);
        }

        $user = [];
        // create 30 user with faker ! Bam!
        for ($i = 0; $i < 30; $i++) {
            $user = new User();
            $user->setFirstName($faker->firstName);
            $user->setLastName($faker->lastName);
            $user->setUsername($user->getFirstName(). ' ' .$user->getLastName());
            
            $users[] = $user;

            $manager->persist($user);
        }

        $comments = [];

        // create 30 comments with faker ! Bam!
        for ($i = 0; $i < 30; $i++) {
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
