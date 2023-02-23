<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\SubCategory;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function add(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    // Retourne les catégrories les sous categories et tous les produits visibles poduct.visibility = 1
    public function findAllVisibleProductsAndCatsAndSubCatsOrderedByListOrder(): array
    {
        return $this->createQueryBuilder('c')

        // je selectionne les catégories, sous-catégories, produits de la catégorie et produits des sous-catégories
        // en utilisant les alias c, sc, cp, scp pour les catégories, sous-catégories, produits de la catégorie et produits des sous-catégories
        // je peux ensuite utiliser ces alias dans les requêtes suivantes (leftJoin, orderBy) pour faire référence aux propriété relationelles des entités
        // et non aux noms des champs de la table category 
        ->select('c, sc, cp, scp')
        // je récupère les sous-catégories de la catégorie qui sont visibles (visibility = 1)
        ->leftJoin('c.subCategories', 'sc')
        // je récupère les produits de la catégorie et de ses sous-catégories qui sont visibles (visibility = 1)
        ->leftJoin('c.products', 'cp', 'WITH', 'cp.visibility = true')
        
        // récupérer uniquement les 4 derniers produits de la catégorie et de ses sous-catégories
        
        // et dont le prix est supérieur à 150  - commenté mais fonctionnel - peut être utilisé pour filtrer les produits avec un paramètre de requête par exemple
        // $price = 150; 
        //->andWhere('cp.sellingPrice > :price')->setParameter('price', $price)
        // ->andWhere('cp.sellingPrice > 100')
        ->leftJoin('sc.products', 'scp', 'WITH', 'scp.visibility = true')
     
        // je trie les catégories et sous-catégories par leur ordre de liste (listOrder)
        ->orderBy('c.listOrder + 0', 'ASC')
        ->addOrderBy('sc.listOrder + 0', 'ASC')
        // je récupère le résultat de la requête
        ->getQuery()
        // je retourne le résultat de la requête
        ->getResult()
        
        ;
    }

    // find all products category and subcategory products where poduct.visibility = 1 
    // and product.sellingPrice > $min and product.sellingPrice < $max
    public function findCatsAndSubCatsProductsByPriceMinMax(int $min, int $max): array
    {
        return $this->createQueryBuilder('c')
        
        // je selectionne les catégories, sous-catégories, produits de la catégorie et produits des sous-catégories
        // en utilisant les alias c, sc, cp, scp pour les catégories, sous-catégories, produits de la catégorie et produits des sous-catégories
        // je peux ensuite utiliser ces alias dans les requêtes suivantes (leftJoin, orderBy) pour faire référence aux propriété relationelles des entités
        // et non aux noms des champs de la table category 
        ->select('c, sc, cp, scp')

        // je récupère les sous-catégories de la catégorie qui sont visibles (visibility = 1) et dont le prix est supérieur à $min et inférieur à $max
        ->leftJoin('c.subCategories', 'sc')
        // je récupère les produits de la catégorie et de ses sous-catégories qui sont visibles (visibility = 1)
        ->leftJoin('c.products', 'cp WITH cp.visibility = true AND cp.sellingPrice BETWEEN :min AND :max')
        ->setParameter('min', $min)
        ->setParameter('max', $max)
        // je récupère les produits des sous-catégories qui sont visibles (visibility = 1) et dont le prix est supérieur à $min et inférieur à $max
        ->leftJoin('sc.products', 'scp WITH scp.visibility = true AND scp.sellingPrice BETWEEN :min AND :max')
        ->setParameter('min', $min)
        ->setParameter('max', $max)
        // je trie les catégories et sous-catégories par leur ordre de liste (listOrder)
        ->orderBy('c.listOrder + 0', 'ASC')
        ->addOrderBy('sc.listOrder + 0', 'ASC')
        // je récupère le résultat de la requête
        ->getQuery()
        // je retourne le résultat de la requête
        ->getResult()
        
        ;
    }

    // find all products category and subcategory products where poduct.visibility = 1 and product.sellingPrice > $min and product.sellingPrice < $max
    // this find all categroies and subcategories including those who have no products in the price range
    public function findAllCatAndSubCatWhereProductIsIneThePriceRange(int $min, int $max): array
    {
        return $this->createQueryBuilder('c')
            ->select('c, cp, sc, scp')
            ->leftJoin('c.products', 'cp', 'WITH', 'cp.visibility = true AND cp.sellingPrice BETWEEN :min AND :max')
            ->setParameter('min', $min)
            ->setParameter('max', $max)
            ->leftJoin('c.subCategories', 'sc')
            ->leftJoin('sc.products', 'scp', 'WITH', 'scp.visibility = true AND scp.sellingPrice BETWEEN :min_1 AND :max_2')
            ->setParameter('min_1', $min)
            ->setParameter('max_2', $max)
            ->getQuery()
            ->getResult()
        ;
    }

    // find only category and subcategory products where poduct.visibility = 1 and product.sellingPrice > $min and product.sellingPrice < $max
    // this don't find category and subcategory without products in this range of price
    public function findOnlyCatAndSubCatWhereProductIsIneThePriceRange(int $min, int $max): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c, cp, sc, scp')
            ->leftJoin('c.products', 'cp')
            ->leftJoin('c.subCategories', 'sc')
            ->leftJoin('sc.products', 'scp');

        $qb->andWhere($qb->expr()->andX(
            $qb->expr()->eq('cp.visibility', true),
            $qb->expr()->between('cp.sellingPrice', ':min', ':max'),
            $qb->expr()->eq('scp.visibility', true),
            $qb->expr()->between('scp.sellingPrice', ':min', ':max'),       
        ));

        $qb->setParameter('min', $min)
        ->setParameter('max', $max)

        ->orderBy('c.listOrder + 0', 'ASC')
        ->addOrderBy('sc.listOrder + 0', 'ASC')
        ;
        
        // J'ai fusionné les deux appels à leftJoin pour ne pas avoir à les répéter, 
        // j'ai ajouté les conditions de visibilité et de prix dans la clause WHERE plutôt 
        // qu'avec le WITH pour éviter de les répéter, j'ai utilisé les expressions 
        // pour les filtres pour éviter les erreurs de syntaxe et j'ai utilisé des alias 
        //pour les paramètres pour éviter les conflits de noms.

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne l'id et le nom des catégories et sous-catégories avec l'id le nom et le prix des produits 
     * si le produit est visible et son prix est compris entre $min et $max
     * 
     *  0 => array:7 [▼
     *   "category_name" => "category 2"
     *   "category_id" => "2"
     *   "subcategory_name" => "SubCat 2"
     *   "subcategory_id" => "2"
     *   "product_name" => "product 137"
     *   "product_id" => "137"
     *   "selling_price" => "171"
     *  ]
     * 
     * @param integer $min
     * @param integer $max
     * @return array
     */
    public function findProdIdProdNameProdPriceFromCatAndSubCat(int $min, int $max) : array
    {   
    
        $conn = $this->getEntityManager()->getConnection(); 

        $sql ="SELECT c.name as category_name, c.id as category_id, sc.name as subcategory_name, sc.id as subcategory_id, p.name as product_name, p.id as product_id, p.selling_price
        FROM category c
        LEFT JOIN sub_category_category scc ON c.id = scc.category_id
        LEFT JOIN sub_category sc ON scc.sub_category_id = sc.id
        LEFT JOIN product p ON (c.id = p.category_id OR sc.id = p.sub_category_id)
        WHERE p.visibility = 1 AND p.selling_price BETWEEN $min AND $max 
        ORDER BY c.list_order + 0 ASC, sc.list_order + 0 ASC
        ";
    
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        // returns an array of arrays (i.e. a raw data set)
        return $resultSet->fetchAllAssociative();
    }

    /**
     * Retourne l'id le nom et le prix des produits d'une catégorie et de ses sous-catégories
     * si le produit est visible et si son prix est compris entre $min et $max
     * si il n'y a pas de produit visible dans la catégorie ou la sous-catégorie, 
     * le produit remonte null pour pouvoir afficher la catégorie et la sous-catégorie
     * et indiquer qu'il n'y a pas de produit visible dans cette catégorie ou sous-catégorie
     *  
     * 0 => array:3 [▼
     *   "name" => "product 77"
     *   "id" => "77"
     *   "selling_price" => "715"
     *  ]
     * 
     * @param integer $min
     * @param integer $max
     * @return array
     */
    public function findOnlyProdIdProdNameProdPriceFromCatAndSubCat(int $min, int $max): array
    {   
        $conn = $this->getEntityManager()->getConnection(); 

        $sql = " SELECT c.name, c.id, sc.name, sc.id, cp.name, cp.id, scp.name, scp.id, scp.selling_price 
        FROM category c 
        LEFT JOIN sub_category_category scc 
        ON c.id = scc.category_id 
        LEFT JOIN sub_category sc 
        ON scc.sub_category_id = sc.id 
        LEFT JOIN product cp ON c.id = cp.category_id 
        LEFT JOIN product scp ON sc.id = scp.sub_category_id 
        WHERE cp.visibility = 1 AND cp.selling_price BETWEEN $min AND $max
        AND scp.visibility = 1 AND scp.selling_price BETWEEN $min AND $max 
        ORDER BY c.list_order + 0 ASC, sc.list_order + 0 ASC ";
    
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        // returns an array of arrays (i.e. a raw data set)
        return $resultSet->fetchAllAssociative();
    }


    /**
     * Retourne l'id le nom et le prix des produits d'une catégorie et de ses sous-catégories
     * si le produit est visible et si son prix est compris entre $min et $max
     * si il n'y a pas de produit visible dans la catégorie ou la sous-catégorie, 
     * le produit remonte null pour pouvoir afficher la catégorie et la sous-catégorie
     * et indiquer qu'il n'y a pas de produit visible dans cette catégorie ou sous-catégorie
     *
     * Cette requête utilise des sous-requêtes pour pré-extraire les données des tables category, sub_category et product
     * qui sont utilisées dans la requête principale. Cela permet de limiter les données qui sont utilisées pour créer 
     * les jointures, ce qui peut améliorer les performances de la requête.
     * 
     *  0 => array:7 [▼
     *   "category_name" => "category 2"
     *   "category_id" => "2"
     *   "subcategory_name" => "SubCat 2"
     *   "subcategory_id" => "2" 
     *   "product_name" => "product 130"
     *   "product_id" => "130"
     *   "selling_price" => "1096"
     * ]
     * 
     * 
     * @param integer $min
     * @param integer $max
     * @return array
     */
    public function findProdIdProdNameProdPriceFromCatAndSubCatSubRequestVersion(int $min,int $max) : array 
    {
        $conn = $this->getEntityManager()->getConnection(); 
        
        $sql = " SELECT c.name as category_name, c.id as category_id, 
                sc.name as subcategory_name, sc.id as subcategory_id, 
                p.name as product_name, p.id as product_id, p.selling_price
        FROM (
        SELECT id, name, list_order FROM category 
        ) c
        LEFT JOIN (
        SELECT category_id, sub_category_id FROM sub_category_category 
        ) scc ON c.id = scc.category_id
        LEFT JOIN (
        SELECT id, name, list_order FROM sub_category 
        ) sc ON scc.sub_category_id = sc.id
        LEFT JOIN (
        SELECT id, name, selling_price, category_id, sub_category_id 
        FROM product WHERE visibility = 1 AND selling_price BETWEEN :min AND :max
        ) p ON (c.id = p.category_id OR sc.id = p.sub_category_id)
        ORDER BY c.list_order + 0 ASC, sc.list_order + 0 ASC
        ";

        $stmt = $conn->prepare($sql);
        // $stmt->bindValue(':min', $min);
        // $stmt->bindValue(':max', $max);
        $resultSet = $stmt->executeQuery(['min' => $min, 'max' => $max]);

        // returns an array of arrays (i.e. a raw data set)
        return $resultSet->fetchAllAssociative();
    }

    // find all cats ordered by listOrder
    public function findAllCatsOrderByListOrder(): array
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('c')
            ->orderBy('c.listOrder + 0', 'ASC');
        return $qb->getQuery()->getResult();
    }

    // find all category id name and listOrder and subcategory id name and listOrder ordered by listOrder
    public function findAllCatsAndSubCatsOrderByListOrder(): array
    {
        $conn = $this->getEntityManager()->getConnection(); 
        $sql = "SELECT c.name as catName, c.id as catId, c.list_order as catOrder, sc.name as subCatName, sc.id as subCatId, sc.list_order as subCatOrder 
        FROM category c 
        LEFT JOIN sub_category_category scc 
        ON c.id = scc.category_id 
        LEFT JOIN sub_category sc 
        ON scc.sub_category_id = sc.id 
        ORDER BY c.list_order + 0 ASC, sc.list_order + 0 ASC ";
    
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        // returns an array of arrays (i.e. a raw data set)
        return $resultSet->fetchAllAssociative();

    }

    // Trouve toutes les catégories et sous-catégories et les ordonne par listOrder pour le menu de navigation
    // retourne un tableau de tableau avec les clés catId, catName, catOrder, subCatId, subCatName, subCatOrder
    //    2 => array:4 [▶
    //         "catId" => 2
    //         "catName" => "category 2"
    //         "catOrder" => "590"
    //         "subCategories" => array:5 [▶
    //             0 => array:3 [▶
    //                 "subCatId" => 3
    //                 "subCatName" => "SubCat 3"
    //                 "subCatOrder" => "1444"
    //             ]
    //             1 => array:3 [▶
    //                 "subCatId" => 1
    //                 "subCatName" => "SubCat 1"
    //                 "subCatOrder" => "4583"
    //             ]
    //         ]
    //     ]   
    //
    //* appelée dans NavService pour construire le menu de navigation
    //* qui retourne une variable twig globale (nav) accessible dans toutes les vues
    public function requestNav(): array
    {
        // query categories and subcategories where subcategory belongs to category
        $qb = $this->createQueryBuilder('c');
        $qb->select('c.id as catId, c.name as catName, c.listOrder as catOrder, sc.id as subCatId, sc.name as subCatName, sc.listOrder as subCatOrder')
        ->leftJoin('c.subCategories', 'sc')
        ->orderBy('c.listOrder + 0', 'ASC')
        ->andWhere('c.subCategories IS NOT EMPTY')
        //->andWhere('sc.products IS NOT EMPTY')
        //->orWhere('c.products IS NOT EMPTY')
        ->addOrderBy('sc.listOrder + 0', 'ASC');
        $result = $qb->getQuery()->getResult();
        // build array with categories and subcategories
        $categories = [];
        foreach ($result as $row) {
            if (!isset($categories[$row['catId']])) {
                $categories[$row['catId']] = [
                    'catId' => $row['catId'],
                    'catName' => $row['catName'],
                    'catOrder' => $row['catOrder'],
                    'subCategories' => []
                ];
            }
            if ($row['subCatId']) {
                $categories[$row['catId']]['subCategories'][] = [
                    'subCatId' => $row['subCatId'],
                    'subCatName' => $row['subCatName'],
                    'subCatOrder' => $row['subCatOrder']
                ];
            }
        }

        return $categories;            
    }

    // Construit un tableau associatif avec les catégories et sous-catégories et les produits associés
    // requête très lente.
    public function testDql(): array
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('c.id as catId, c.name as catName, c.listOrder as catOrder, sc.id as subCatId, sc.name as subCatName, sc.listOrder as subCatOrder, p.id as productId, p.name as productName, p.sellingPrice as sellingPrice')
        ->leftJoin('c.subCategories', 'sc')
        ->leftJoin('c.products', 'p', 'WITH', 'p.category = c')
        ->leftJoin('sc.products', 'ps', 'WITH', 'ps.subCategory = sc')
        ->orderBy('c.listOrder + 0', 'ASC')
        ->addOrderBy('sc.listOrder + 0', 'ASC');

        $results = $qb->getQuery()->getResult();
       
        foreach ($results as $res) {
        // initialisation du tableau type de catégories et sous catégories
        // qui sera retourné et rempli avec les données de la requête en fonction des catégories et sous catégories trouvées
        // ainsi que les produits associés à chaque catégorie et sous catégorie
            
            // je traite les catégories principales en commençant par vérifier que la catégorie n'est pas déjà dans le tableau
            // si elle n'y est pas, je l'ajoute avec les données des sous catégories et un tableau vide pour les produits
            // le tableau des sous catégories est initialisé avec les données de la sous catégorie trouvée
            // le tableau des produits est initialisé vide pour le moment car je ne connais pas encore les produits associés à cette sous catégorie
            // si la catégorie est déjà dans le tableau, je ne fais rien car elle a déjà été traitée et ajoutée au tableau des catégories et sous catégories
           
            if (!isset($categories[$res['catId']])) {
                $categories[$res['catId']] = [
                    'catId' => $res['catId'],
                    'catName' => $res['catName'],
                    'catOrder' => $res['catOrder'],
                    'products' => [],
                        'subCategories' => [
                            'subCatId' => $res['subCatId'],
                            'subCatName' => $res['subCatName'],
                            'subCatOrder' => $res['subCatOrder'],
                            'products' => []
                        ]
                    
                ];
            }
             // je traite les sous catégories de la catégorie principale en commençant par vérifier que la sous catégorie n'est pas déjà dans le tableau
             // le reste est similaire à la catégorie principale
            if ($res['subCatId'] != null) {
                if (!isset($categories[$res['catId']]['subCategories'][$res['subCatId']])) {
                    $categories[$res['catId']]['subCategories'][$res['subCatId']] = [
                        'subCatId' => $res['subCatId'],
                        'subCatName' => $res['subCatName'],
                        'subCatOrder' => $res['subCatOrder'],
                        'products' => []
                    ];
                }
            }
            // je traite les produits de la catégorie principale en commençant par vérifier que le produit n'est pas déjà dans le tableau
            // je vérifie aussi que le produit n'est pas déjà dans le tableau des produits de la catégorie principale
            // si le produit n'est pas déjà dans le tableau des produits de la catégorie principale, je l'ajoute avec les données du produit
            // si le produit est déjà dans le tableau des produits de la catégorie principale, je ne fais rien car il a déjà été traité et ajouté au tableau des produits de la catégorie principale
            // si le produit n'est pas déjà dans le tableau des produits de la catégorie principale, je ne fais rien car il a déjà été traité et ajouté au tableau des produits de la catégorie principale
            if ($res['productId'] != null) {
                if (!in_array($res['productId'], $categories[$res['catId']]['products'])) {
                    $categories[$res['catId']]['products'][$res['productId']] = [
                        'productId' => $res['productId'],
                        'productName' => $res['productName'],
                        'sellingPrice' => $res['sellingPrice']
                    ];
                }
            }
            // je traite les produits des sous-catégories de la catégorie principale en commençant par vérifier que le produit n'est pas déjà dans le tableau
            // je vérifie aussi que le produit n'est pas déjà dans le tableau des produits de la sous catégorie 
            // si le produit n'est pas déjà dans le tableau des produits de la sous catégorie, je l'ajoute avec les données du produit
            // si le produit est déjà dans le tableau des produits de la sous catégorie, je ne fais rien car il a déjà été traité et ajouté au tableau des produits de la sous catégorie
            if ($res['productId'] != null) {
                if (!in_array($res['productId'], $categories[$res['catId']]['subCategories']['products'])) {
                    $categories[$res['catId']]['subCategories']['products'][$res['productId']] = [
                        'productId' => $res['productId'],
                        'productName' => $res['productName'],
                        'sellingPrice' => $res['sellingPrice']
                    ];
                }
            }
            
        }
    return $categories;
    }
    
    // Construit un tableau associatif avec les catégories et sous-catégories et les produits associés
    // requête trois fois plus rapide en SQL par rapport à la requête en DQL testDql()
    public function testSql(): array
    {   
        // je convertis les id en entier en utilisant CAST(c.id as UNSIGNED) pour éviter que le tri ne se fasse en string

        $conn = $this->getEntityManager()->getConnection(); 
        $sql = " SELECT CAST(c.id as UNSIGNED) as catId, c.name as catName, c.list_order as catOrder, 
        CAST(sc.id as UNSIGNED) as subCatId, sc.name as subCatName, sc.list_order as subCatOrder, 
        CAST(p.id as UNSIGNED) as productId, p.name as productName, p.selling_price as sellingPrice
        FROM category c
        LEFT JOIN sub_category sc ON c.id = sc.id
        LEFT JOIN product p ON c.id = p.category_id
        LEFT JOIN product ps ON sc.id = ps.sub_category_id
        ORDER BY c.list_order + 0 ASC, sc.list_order + 0 ASC";
    
        $stmt = $conn->prepare($sql);
        $results = $stmt->executeQuery();
        $results = $results->fetchAllAssociative();
     
        foreach ($results as $res) {
        // initialisation du tableau type de catégories et sous catégories
        // qui sera retourné et rempli avec les données de la requête en fonction des catégories et sous catégories trouvées
        // ainsi que les produits associés à chaque catégorie et sous catégorie
            
            // je traite les catégories principales en commençant par vérifier que la catégorie n'est pas déjà dans le tableau
            // si elle n'y est pas, je l'ajoute avec les données des sous catégories et un tableau vide pour les produits
            // le tableau des sous catégories est initialisé avec les données de la sous catégorie trouvée
            // le tableau des produits est initialisé vide pour le moment car je ne connais pas encore les produits associés à cette sous catégorie
            // si la catégorie est déjà dans le tableau, je ne fais rien car elle a déjà été traitée et ajoutée au tableau des catégories et sous catégories
           
            if (!isset($categories[$res['catId']])) {
                $categories[$res['catId']] = [
                    'catId' => $res['catId'],
                    'catName' => $res['catName'],
                    'catOrder' => $res['catOrder'],
                    'products' => [],
                        'subCategories' => [
                            'subCatId' => $res['subCatId'],
                            'subCatName' => $res['subCatName'],
                            'subCatOrder' => $res['subCatOrder'],
                            'products' => []
                        ]
                    
                ];
            }
             // je traite les sous catégories de la catégorie principale en commençant par vérifier que la sous catégorie n'est pas déjà dans le tableau
             // le reste est similaire à la catégorie principale
            if ($res['subCatId'] != null) {
                if (!isset($categories[$res['catId']]['subCategories'][$res['subCatId']])) {
                    $categories[$res['catId']]['subCategories'][$res['subCatId']] = [
                        'subCatId' => $res['subCatId'],
                        'subCatName' => $res['subCatName'],
                        'subCatOrder' => $res['subCatOrder'],
                        'products' => []
                    ];
                }
            }
            // je traite les produits de la catégorie principale en commençant par vérifier que le produit n'est pas déjà dans le tableau
            // je vérifie aussi que le produit n'est pas déjà dans le tableau des produits de la catégorie principale
            // si le produit n'est pas déjà dans le tableau des produits de la catégorie principale, je l'ajoute avec les données du produit
            // si le produit est déjà dans le tableau des produits de la catégorie principale, je ne fais rien car il a déjà été traité et ajouté au tableau des produits de la catégorie principale
            // si le produit n'est pas déjà dans le tableau des produits de la catégorie principale, je ne fais rien car il a déjà été traité et ajouté au tableau des produits de la catégorie principale
            if ($res['productId'] != null) {
                if (!in_array($res['productId'], $categories[$res['catId']]['products'])) {
                    $categories[$res['catId']]['products'][$res['productId']] = [
                        'productId' => $res['productId'],
                        'productName' => $res['productName'],
                        'sellingPrice' => $res['sellingPrice']
                    ];
                }
            }
            // je traite les produits des sous-catégories de la catégorie principale en commençant par vérifier que le produit n'est pas déjà dans le tableau
            // je vérifie aussi que le produit n'est pas déjà dans le tableau des produits de la sous catégorie 
            // si le produit n'est pas déjà dans le tableau des produits de la sous catégorie, je l'ajoute avec les données du produit
            // si le produit est déjà dans le tableau des produits de la sous catégorie, je ne fais rien car il a déjà été traité et ajouté au tableau des produits de la sous catégorie
            if (intval($res['productId']) != null) {
                if (!in_array(intval($res['productId']), $categories[$res['catId']]['subCategories']['products'])) {
                    $categories[$res['catId']]['subCategories']['products'][intval($res['productId'])] = [
                        'productId' => $res['productId'],
                        'productName' => $res['productName'],
                        'sellingPrice' => $res['sellingPrice']
                    ];
                }
            }
            
        }
    return $categories;
    }

                
    // Get all product from subcategory 
    public function getSubCatProducts($subCatId)
    {
        $query = $this->createQueryBuilder('p')
            ->select('p.id', 'p.name', 'p.sellingPrice')
            ->join('p.subCategory', 's')
            ->where('s.id = :subCatId')
            ->setParameter('subCatId', $subCatId)
            ->getQuery();
        return $query->getResult();
    }


    // retourne les categories et les 5 derniers produits de chaque sous catégorie
    // mais moins performant que la requête findAll()
    public function test(): array
    {
        $subQb = $this->getEntityManager()->createQueryBuilder();
        $subQb->select('sc.id')
            ->from(SubCategory::class, 'sc')
            ->innerJoin(Product::class, 'p', 'WITH', 'sc = p.subCategory')
            ->groupBy('sc.id')
            ->orderBy('MAX(p.id)', 'DESC')
            ->setMaxResults(5);

        $qb = $this->createQueryBuilder('cat');
        $qb->select('cat', 'scat', 'prod')
            ->join('cat.subCategories', 'scat')
            ->join('scat.products', 'prod')
            ->where($qb->expr()->in('scat.id', $subQb->getDQL()))
            ->orderBy('cat.listOrder + 0', 'ASC');

        return $qb->getQuery()->getResult();

    }

    public function homeCats(): array
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('c')
            ->orderBy('c.listOrder + 0', 'ASC')
            ->andWhere('c.showOnHome = true');
        return $qb->getQuery()->getResult();
    }

    
    
    
    
    
    

//    /**
//     * @return Category[] Returns an array of Category objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Category
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
