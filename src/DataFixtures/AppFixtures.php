<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Entity\Product;
use App\Entity\User;

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
        for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $product->setName('product '.($i +1));
            $product->setCategory($cats[rand(0, count($cats) - 1)]);
            $product->setSubCategory($subCats[rand(0, count($subCats) - 1)]);
            
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

        $manager->flush();
    }
}
