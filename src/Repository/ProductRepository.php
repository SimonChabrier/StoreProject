<?php

namespace App\Repository;

use App\Entity\Product;
use InvalidArgumentException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
     * Return 8 products from the same subcategory
     * @return Product[] Returns an array of Product objects
     */
    public function relatedProducts($subCatId): array
    {
        $qb = $this->createQueryBuilder('p');

        $qb->select('p')
            ->where('p.visibility = 1 AND p.inStockQuantity > 0')
            ->andWhere('p.subCategory = :subCategory')
            ->setParameter('subCategory', $subCatId)
            ->setMaxResults(8)
            ->orderBy('p.id', 'asc');

        return $qb->getQuery()->getResult();
    }

    /**
     * Return products data array
     * filtered by price range
     * 
     * @param [int] $min
     * @param [int] $max
     * @return array
     */
    public function filterByPriceRange($min, $max): array
    {
        $entityManager = $this->getEntityManager();

        $sql = '
            SELECT 
                p.name AS product_name,
                p.id AS product_id,
                p.selling_price,
                c.name AS category_name,
                c.id AS category_id,
                sc.name AS subcategory_name,
                sc.id AS subcategory_id
            FROM 
                product p
            LEFT JOIN 
                category c ON p.category_id = c.id
            LEFT JOIN 
                sub_category sc ON p.sub_category_id = sc.id
            WHERE 
                CAST(REPLACE(p.selling_price, " ", "") AS DECIMAL) BETWEEN :min AND :max
            ORDER BY 
                c.list_order + 0 ASC, sc.list_order + 0 ASC
        ';

        $query = $entityManager->getConnection()->prepare($sql);
        $results = $query->execute(['min' => $min, 'max' => $max]);
        
        return $results->fetchAll();
    }

    /**
     * Return products data array
     * filtered by price range
     * 
     * @param [int] $min
     * @param [int] $max
     * @return Product[] Returns an array of Product objects
     */
    public function findByPriceRange($min, $max): array
    {
        $query = $this->createQueryBuilder('p')
            ->select('p')
            ->addSelect('p.name AS product_name', 'p.id AS product_id', 
            'p.sellingPrice', 'c.name AS category_name', 
            'c.id AS category_id', 'sc.name AS subcategory_name', 
            'sc.id AS subcategory_id')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.subCategory', 'sc')
            ->where('p.sellingPrice BETWEEN :min AND :max')
            ->setParameter('min', $min)
            ->setParameter('max', $max)
            ->orderBy('c.listOrder + 0', 'ASC')
            ->addOrderBy('sc.listOrder + 0', 'ASC')
            ->getQuery();

            return $query->getResult();
    }

    /**
     * Return products by search term
     *
     * @param [string] $term
     * @return Product[] Returns an array of Product objects
     */
    public function search(string $term): array
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.name LIKE :searchTerm')
            ->setParameter('searchTerm', '%'.$term.'%')
            ->getQuery();
            return $query->getResult();
            // note sytaxe pour plus tard :
            // ->andWhere('p.name LIKE :searchTerm
            //     OR p.property1 LIKE :searchTerm
            //     OR p.property2 LIKE :searchTerm')
    }

    /**
     * Retrun all products id
     * @return Product[] Returns an array of Product objects
     */
    public function findAllProductsId(): array
    {
        $query = $this->createQueryBuilder('p')
            ->select('p, p.id as product_id')
            ->getQuery();
            return $query->getResult();
    }

    /**
     * Return products data array to paginate products list
     * offset start at 0 and perpage is the number of products to display
     * @var perpage int 
     * @var offset int
     * @return Product[] Returns an array of Product objects
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

    /**
     * Return a product list by $count number
     * @var count int
     * @return Product[] Returns an array of Product objects
     */
    public function findLastProduct(int $count): array
    {
        $query = $this->createQueryBuilder('p')

                ->select('p')
                ->orderBy('p.id', 'DESC')
                ->setMaxResults($count)
                ->getQuery();

            return $query->getResult();
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

    /**
     * Return all visible products
     *
     * @return Product[] Returns an array of Product objects
     */
    public function findAllVisibleProdcts(): array
    {
        $qb = $this->createQueryBuilder('p');

        $qb->select('p')   
            ->andWhere('p.visibility = 1')
            ->orderBy('p.id', 'asc');
            
        return $qb->getQuery()->getResult();
    }  

}
