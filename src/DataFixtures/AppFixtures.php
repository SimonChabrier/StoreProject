<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Entity\Product;
use App\Entity\ProductType;
use App\Entity\Brand;

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
        $this->connexion->executeQuery('TRUNCATE TABLE brand');
    }

    public function load(ObjectManager $manager): void
    {
        $this->truncate();

        $faker = Faker::create('fr_FR');

        $categories = [];
        $subCategories = [];
        $productTypes = [];
        $brands = [];


        // create the main snakers categories names
        $rootCategoriesNames = [
            'Nouveautés',
            'Homme',
            'Femme',
            'Enfant',
            'Accessoires',
            'Soldes',
        ];

        // create 10 main snakers sub categories names for each main categories

        $subCategoriesNames = [
            "Running",
            "Lifestyle",
            "Ville",
            //"Trail",
            "Randonnée",
            "Training",
            //"Fitness",
            //"Basket",
        ]; 

        // create 10 main snakers product types names for each main categories
        $productTypesNames = [
            "Running" => [
                "course à pied",
                "trail",
            ],
            "Lifestyle" => [
                "chill",
                "classics",
                "skate",
                "streetwear",
            ],
            "Ville" => [
                "richelieu",
                "derbies",
                "bateaux",
                "boots",
                "bottines",
                "mocassins",
                "bottes",
                "snakers",
            ],
            "Randonnée" => [
                "randonnée",
                "montagne",
            ],
            "Training" => [
                "cross-training",
                "musculation",
                "athlétisme",
                "fitness",
            ],
            // "Basket" => [
            //     "basket-ball",
            //     "skate",
            //     "streetwear",
            // ],
        ];
        //     foreach ($types[$subCategoryName] as $type) {...}

        $brandsNames = [
            'Nike',
            'Adidas',
            'Adidas Originals',
            'Clark\'s Originals',
            'Puma',
            'Reebok',
            'New Balance',
            'Asics',
            'Vans',
            'Converse',
            'Fila',
            'Salomon',
            'Le Coq Sportif',
            'Lacoste',
            'Kappa',
            'Timberland',
            'Geox',
            'Tommy Hilfiger',
            'Levi\'s',
            'Wrangler',
            'Pepe Jeans',
            'Diesel',
        ];
        
        $subCategoriesProductNames = [
            "Running" => [
                "Adidas Ultraboost",
                "Nike Zoom Pegasus",
                "Saucony Ride",
                "Brooks Ghost",
                "Asics Gel Nimbus",
                "New Balance Fresh Foam",
                "Under Armour Charged Pursuit",
                "Hoka One One Clifton",
                "Salomon Sense",
                "Columbia Montrail Fluidflex",
                "Merrell Bare Access",
                "La Sportiva Akasha",
                "Keen Oakridge",
                "Salewa Rapace",
                "Lowa Renegade",
                "Scarpa Spin",
                "Ecco Terra",
                "Inov-8 Terraultra",
                "Altra Torin",
                "Boreal Joker",
                "Five Ten Anasazi",
                "Black Diamond Momentum",
                "Mad Rock Drifter",
                "Red Chili Voltage",
                "Butora Acro",
                "Tenaya Oasi",
                "La Sportiva Solution",
                "Five Ten Hiangle",
                "Black Diamond Zone",
                "Mad Rock M5",
                "Red Chili Durango"
            ],
            "Lifestyle" => [
                "Converse Chuck Taylor",
                "Vans Old Skool",
                "Puma Cali",
                "Reebok Classic",
                "Jordan 1",
                "Timberland 6-Inch",
                "UGG Classic Mini",
                "Dr. Martens 1460",
                "Sperry Top-Sider",
                "Nike Air Max",
                "Adidas Superstar",
                "Vans Slip-On",
                "Puma Suede",
                "Reebok Club C",
                "Jordan XXXII",
                "Timberland Euro Hiker",
                "UGG Classic Tall",
                "Dr. Martens Jadon",
                "Sperry Bahama",
                "Converse All Star",
                "Adidas Adizero",
                "Nike Pegasus",
                "Boreal Kira",
            ],
            "Ville" => [
                "Loafers",
                "Oxford Shoes",
                "Derby Shoes",
                "Brogues",
                "Monk Straps",
                "Boat Shoes",
                "Wingtips",
                "Chelsea Boots",
                "Chukka Boots",
                "Dress Boots",
                "Snow Boots",
                "Rain Boots",
                "Hiking Boots",
                "Work Boots",
                "Combat Boots",
                "Ankle Boots",
                "Mid-Calf Boots",
                "Over-the-Knee Boots",
                "Sneakers",
                "Running Shoes",
                "Cross-Training Shoes",
                "Basketball Shoes",
                "Tennis Shoes",
                "Gym Shoes",
                "Walking Shoes",
                "Cycling Shoes",
                "Ski Boots",
                "Snowboarding Boots",
                "Ice Skates",
                "Inline Skates",
                "Roller Skates"
            ],
            "Randonnée" => [
                "Salewa Rapace",
                "Lowa Renegade",
                "Scarpa Spin",
                "Ecco Terra",
                "Inov-8 Terraultra",
                "Altra Torin",
                "Boreal Joker",
                "Five Ten Anasazi",
                "Black Diamond Momentum",
                "Mad Rock Drifter",
                "Red Chili Voltage",
                "Butora Acro",
                "Tenaya Oasi",
                "La Sportiva Solution",
                "Five Ten Hiangle",
                "Black Diamond Zone",
                "Mad Rock M5",
                "Red Chili Durango",
                "Adidas Terrex",
                "Nike ACG",
                "The North Face Hedgehog",
                "Merrell Moab",
                "Columbia Redmond",
                "Salomon X Ultra",
                "Hoka One One Stinson",
                "Under Armour Horizon",
                "New Balance Nitrel",
                "Asics Gel-Kayano",
                "Brooks Cascadia",
                "Saucony Xodus",
                "Nike Air Zoom"
            ],
            "Training" => [
                "Nike Metcon",
                "Adidas Adipower",
                "Under Armour TriBase",
                "Reebok Crossfit",
                "Nike Free X Metcon",
                "Adidas Powerlift",
                "Under Armour Project Rock",
                "Reebok Nano",
                "Nike Zoom Train Command",
                "Adidas Pureboost",
                "Under Armour HOVR",
                "Reebok Legacy Lifter",
                "Nike Zoom Pegasus Turbo",
                "Adidas Ultraboost 21",
                "Under Armour Micro G",
                "Reebok Crossfit Speed TR",
                "Nike React Infinity",
                "Adidas Adizero Adios",
                "Under Armour HOVR Phantom",
                "Reebok Harmony Road",
                "Nike Zoom Fly 3",
                "Adidas Solar Boost",
                "Under Armour Charge 4",
                "Reebok Forever Floatride Energy"
            ]
        ];

        //////////////////////////////////////////////////////////////////////////////
        // create the main shoes categories
        for ($i = 0; $i < count($rootCategoriesNames); $i++) {
            $category = new Category();
            
            $category->setName($rootCategoriesNames[$i]);
            $category->setListOrder($i);

            $categories[] = $category;
            $manager->persist($category);
        }

        // boucler sur les categories pour créer les sous categories pour homme femme et enfant
        foreach ($categories as $category) {
            foreach ($subCategoriesNames as $subCategoryName) {

                $category->getName();
                if($category->getName() == 'Nouveautés' || $category->getName() == 'Soldes' || $category->getName() == 'Accessoires') {
                    continue;
                }
                
                $subCategory = new SubCategory();
                $subCategory->setName($subCategoryName);
                                
                $subCategory->addCategory($category);
                
                $subCategories[] = $subCategory;
                $manager->persist($subCategory);
            }
        }
        
        // boucler sur les sous categories pour créer les types de produits pour homme femme et enfant
        foreach ($subCategories as $subCategory) {
            foreach ($productTypesNames[$subCategory->getName()] as $productTypeName) {
                $productType = new ProductType();
                $productType->setName($productTypeName);
                //$productType->addSubCategory($subCategory);
                
                $productTypes[] = $productType;
                $manager->persist($productType);
            }
        }
        
        // créer les marques de chaussures
        foreach ($brandsNames as $brandName) {
            $brand = new Brand();
            $brand->setName($brandName);
            
            $brands[] = $brand;
            $manager->persist($brand);
        }

        $runningProducts = [];
        
        foreach ($subCategoriesProductNames['Running'] as $productName) {
            $product = new Product();
            $product->setName($productName);
            $product->setSellingPrice(mt_rand(50, 200));
            
            $product->setBrand($brands[mt_rand(0, count($brands) - 1)]);

            $product->setProductType($productTypes[mt_rand(0, count($productTypes) - 1)]);
            $product->setCategory($categories[rand(0, 2)]);
            $product->setSubCategory($subCategories[rand(0, count($subCategories) - 1)]);
           
            $product->setInStockQuantity(rand(1, 10));
            $product->setBuyPrice($faker->numberBetween(80, 1000) * 0.8);
            $margin = [1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7];
            $product->setSellingPrice(sprintf('%0.2f', $product->getbuyPrice() * $margin[rand(0, count($margin) - 1)]));
            $product->setCatalogPrice(sprintf('%0.2f', $product->getSellingPrice() * 1.1));

            
            $runningProducts[] = $product;
            $manager->persist($product);
        }

        $lifeStyleProducts = [];

        foreach ($subCategoriesProductNames['Lifestyle'] as $productName) {
            $product = new Product();
            $product->setName($productName);
            $product->setSellingPrice(mt_rand(50, 200));
            
            $product->setBrand($brands[mt_rand(0, count($brands) - 1)]);
            $product->setProductType($productTypes[mt_rand(0, count($productTypes) - 1)]);
            $product->setCategory($categories[rand(0, 2)]);
            $product->setSubCategory($subCategories[rand(0, count($subCategories) - 1)]);

            $product->setInStockQuantity(rand(1, 10));
            $product->setBuyPrice($faker->numberBetween(80, 1000) * 0.8);
            $margin = [1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7];
            $product->setSellingPrice(sprintf('%0.2f', $product->getbuyPrice() * $margin[rand(0, count($margin) - 1)]));
            $product->setCatalogPrice(sprintf('%0.2f', $product->getSellingPrice() * 1.1));

            
            $lifeStyleProducts[] = $product;
            $manager->persist($product);
        }

        $classiqueProducts = [];

        foreach ($subCategoriesProductNames['Ville'] as $productName) {
            $product = new Product();
            $product->setName($productName);
            $product->setSellingPrice(mt_rand(50, 200));
            
            $product->setBrand($brands[mt_rand(0, count($brands) - 1)]);
            $product->setProductType($productTypes[mt_rand(0, count($productTypes) - 1)]);
            $product->setCategory($categories[rand(0, 2)]);
            $product->setSubCategory($subCategories[rand(0, count($subCategories) - 1)]);

            $product->setInStockQuantity(rand(1, 10));
            $product->setBuyPrice($faker->numberBetween(80, 1000) * 0.8);
            $margin = [1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7];
            $product->setSellingPrice(sprintf('%0.2f', $product->getbuyPrice() * $margin[rand(0, count($margin) - 1)]));
            $product->setCatalogPrice(sprintf('%0.2f', $product->getSellingPrice() * 1.1));

            
            $classiqueProducts[] = $product;
            $manager->persist($product);
        }

        $randonneeProducts = [];

        foreach ($subCategoriesProductNames['Randonnée'] as $productName) {
            $product = new Product();
            $product->setName($productName);
            $product->setSellingPrice(mt_rand(50, 200));
            
            $product->setBrand($brands[mt_rand(0, count($brands) - 1)]);
            $product->setProductType($productTypes[mt_rand(0, count($productTypes) - 1)]);
            $product->setCategory($categories[rand(0, 2)]);
            $product->setSubCategory($subCategories[rand(0, count($subCategories) - 1)]);

            $product->setInStockQuantity(rand(1, 10));
            $product->setBuyPrice($faker->numberBetween(80, 1000) * 0.8);
            $margin = [1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7];
            $product->setSellingPrice(sprintf('%0.2f', $product->getbuyPrice() * $margin[rand(0, count($margin) - 1)]));
            $product->setCatalogPrice(sprintf('%0.2f', $product->getSellingPrice() * 1.1));

            
            $randonneeProducts[] = $product;
            $manager->persist($product);
        }

        $trainingProducts = [];

        foreach ($subCategoriesProductNames['Training'] as $productName) {
            $product = new Product();
            $product->setName($productName);
            $product->setSellingPrice(mt_rand(50, 200));
            
            $product->setBrand($brands[mt_rand(0, count($brands) - 1)]);
            $product->setProductType($productTypes[mt_rand(0, count($productTypes) - 1)]);
            $product->setCategory($categories[rand(0, 2)]);
            $product->setSubCategory($subCategories[rand(0, count($subCategories) - 1)]);

            $product->setInStockQuantity(rand(1, 10));
            $product->setBuyPrice($faker->numberBetween(80, 1000) * 0.8);
            $margin = [1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7];
            $product->setSellingPrice(sprintf('%0.2f', $product->getbuyPrice() * $margin[rand(0, count($margin) - 1)]));
            $product->setCatalogPrice(sprintf('%0.2f', $product->getSellingPrice() * 1.1));

            
            $trainingProducts[] = $product;
            $manager->persist($product);
        }

        foreach ($runningProducts as $product) {
            $product->setSubCategory($subCategories[0]);
        }


        $products = array_merge($runningProducts, $lifeStyleProducts, $classiqueProducts, $randonneeProducts, $trainingProducts);
        // find the subcategories 
        foreach ($products as $product) {
            $subCategory = $product->getSubCategory()->getName();

            if ($subCategory === 'Running') {
                // 2 product types for running
                $product->setProductType($productTypes[rand(0, 1)]);
            } 
            if ($subCategory === 'Lifestyle') {
                // 4 product types for lifestyle
                $product->setProductType($productTypes[rand(2, 5)]);
            }
            if ($subCategory === 'Ville') {
                // 8 product types for ville
                $product->setProductType($productTypes[rand(6, 13)]);
            }
            if ($subCategory === 'Randonnée') {
                // 2 product types for randonnée
                $product->setProductType($productTypes[rand(14, 15)]);
            }
            if ($subCategory === 'Training') {
                // 4 product types for training
                $product->setProductType($productTypes[rand(16, 19)]);
            }

            
        }

        // set the products to the subcategories 

        $manager->flush();
    } // end function load
}// end class