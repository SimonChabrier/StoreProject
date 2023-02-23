<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function add(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne les produits visibles
     *
     * @return array
     */
    public function findAllVisibleProdcts(): array
    {
        $qb = $this->createQueryBuilder('p');

        $qb->select('p')   
            ->andWhere('p.visibility = 1')
            ->orderBy('p.id', 'asc');
            
        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne 4 produits liées à la catégorie du produit courant
     * dont la visibilité est true et la quentité en stock est supérieure à 0
     * @return array
     */
    public function relatedProducts($subCatId): array
    {
        $qb = $this->createQueryBuilder('p');

        $count = $qb->select('count(p.id)')
            ->andWhere('p.visibility = 1 AND p.inStockQuantity > 0')
            ->andWhere('p.subCategory = :subCategory')
            ->setParameter('subCategory', $subCatId)
            ->getQuery()
            ->getSingleScalarResult();

        $qb->select('p')
            ->andWhere('p.visibility = 1 AND p.inStockQuantity > 0')
            ->andWhere('p.subCategory = :subCategory')
            ->setParameter('subCategory', $subCatId)
            ->setFirstResult(rand(0, $count - 4))
            ->setMaxResults(4)
            
            ->orderBy('p.id', 'asc');
        return $qb->getQuery()->getResult();
    }

    // find all products category and subcategory products where visibility = 1
    public function test(): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('p, c, sc')
        ->join('p.category', 'c')
        ->join('c.subCategories', 'sc') 
        ->where('p.visibility = 1')
        ->orderBy('c.listOrder + 0', 'ASC')
        ->addOrderBy('sc.listOrder + 0', 'ASC');

        return $qb->getQuery()->getResult();    
    }

    // find all products category and subcategory products where visibility = 1 and product.sellingPrice in between $min and $max

    /**
     * Retourne les produits en fonction du prix min et max
     *
     * @param [type] $min
     * @param [type] $max
     * @return array
     */
    public function findProductsByPriceMinMax($min, $max): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('p, c, sc')

        ->join('p.category', 'c')
        ->join('c.subCategories', 'sc') 

        ->where('p.visibility = 1')
        ->andWhere('p.sellingPrice BETWEEN :min AND :max')

        ->setParameter('min', $min)
        ->setParameter('max', $max)

        ->orderBy('c.listOrder + 0', 'ASC')
        ->addOrderBy('sc.listOrder + 0', 'ASC');

        return $qb->getQuery()->getResult();    
    }

    /**
     * Retourne les produits 
     * en fonction du terme de recherche
     *
     * @param [string] $term
     * @return void
     */
    public function search(string $term): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.name LIKE :searchTerm')
            //->leftJoin('p.category', 'pc')
            ->setParameter('searchTerm', '%'.$term.'%')
            ->getQuery()
            ->execute();

            // note sytaxe pour plus tard :
            // ->andWhere('p.name LIKE :searchTerm
            //     OR p.property1 LIKE :searchTerm
            //     OR p.property2 LIKE :searchTerm')
    }

    /**
     * Retroune l'id pour l'ensemble des produits
     * @return []
     */
    public function findAllProductsId(): array
    {
        $query = $this->createQueryBuilder('p')
            ->select('p.id')
            ->getQuery();
        
            return $query->getResult();
    }

    /**
     * Retroune l'id et le name pour le nombre de produit
     * demandé 
     * @var perpage int 
     * @var offset int
     * @return []
     */
    public function findPaginateProducts(int $perPage, int $offset): array
    {
        $query = $this->createQueryBuilder('p')
            ->select('p.id, p.name')
            ->setFirstResult($offset)
            ->setMaxResults($perPage)
            ->orderBy('p.id', 'ASC')
            ->getQuery();

            return $query->getResult();
    }


//    /**
//     * @return Product[] Returns an array of Product objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
