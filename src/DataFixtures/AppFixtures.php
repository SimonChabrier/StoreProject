<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Entity\Product;
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
        
        // create 20 products! Bam!

        $products = [];

        for ($i = 0; $i < 200; $i++) {
            $product = new Product();
            $product->setName('product '.($i +1));
            $product->setBuyPrice($faker->numberBetween(80, 1000));
            $product->setSellingPrice(floor($product->getbuyPrice() + ($product->getbuyPrice() * 0.2)));
            $product->setCatalogPrice(floor($product->getSellingPrice() + ($product->getbuyPrice() * 0.1)));
            $product->setCategory($cats[rand(0, count($cats) - 1)]);
            $product->setSubCategory($subCats[rand(0, count($subCats) - 1)]);
            $product->setVisibility(rand(0,1));
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
