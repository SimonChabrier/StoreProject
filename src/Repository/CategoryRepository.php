<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\SubCategory;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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


    // find all products category and subcategory products where poduct.visibility = 1
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

    // find all products category and subcategory products where poduct.visibility = 1 and product.sellingPrice > $min and product.sellingPrice < $max
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


    public function chatGPT(int $min, int $max): array
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

public function chatGPT2(int $min, int $max): array
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


    // ->select('c, sc, cp, scp')
    //     ->join('c.subCategories', 'sc')

    //     ->join('c.products', 'cp', 'WITH', 'cp.visibility = true')

    //     // filter category products by price range min and max
    //     ->where('cp.sellingPrice BETWEEN :min AND :max')
    //     ->setParameter('min', $min)
    //     ->setParameter('max', $max)

    //     // filter subcategory products by price range min and max
    //     ->join('sc.products', 'scp', 'WITH', 'scp.visibility = true')
    //     ->andWhere('scp.sellingPrice BETWEEN :min AND :max')
    //     ->setParameter('min', $min)
    //     ->setParameter('max', $max)

    //     // je trie les catégories et sous-catégories par leur ordre de liste (listOrder)
    //     ->orderBy('c.listOrder + 0', 'ASC')
    //     ->addOrderBy('sc.listOrder + 0', 'ASC')
    //     // je récupère le résultat de la requête
    //     ->getQuery()
    //     // je retourne le résultat de la requête
    //     ->getResult()
    





    // find all cats ordered by listOrder
    public function findAllCatsOrderByListOrder(): array
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('c')
            ->orderBy('c.listOrder + 0', 'ASC');
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
