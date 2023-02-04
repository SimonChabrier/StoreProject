<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\SubCategory;
use PhpParser\Node\Expr\Cast;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<SubCategory>
 *
 * @method SubCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubCategory[]    findAll()
 * @method SubCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubCategory::class);
    }

    public function add(SubCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SubCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    
    // En utilisant l'expression arithmétique sc.listOrder + 0, on convertit implicitement 
    // la propriété sc.listOrder en nombre pour l'utiliser dans l'ordre de tri.
    public function getSubCatsOrderByListOrder(): array
    {
        $qb = $this->createQueryBuilder('sc');
        $qb->select('sc')
            ->orderBy('sc.listOrder + 0', 'ASC');
        return $qb->getQuery()->getResult();
    }

    // find last five products by subcategory
    public function findLastFiveProductsBySubCat($id): array
    {
        $qb = $this->createQueryBuilder('sc');
        $qb->select('p')
            ->join('sc.products', 'p')
            ->where('sc.id = :id')
            ->setParameter('id', $id)
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(5);
        return $qb->getQuery()->getResult();
    }

    // retourne les sous-catégorie et les 5 derniers produits de chaque sous-catégorie
    public function findAllSubCatsLastFiveProducts(): array
    {
        $subQb = $this->getEntityManager()->createQueryBuilder();
        $subQb->select('sc.id')
            ->from(SubCategory::class, 'sc')
            ->innerJoin(Product::class, 'p', 'WITH', 'sc = p.subCategory')
            ->groupBy('sc.id')
            ->orderBy('MAX(p.id)', 'DESC')
            ->setMaxResults(5);

        $qb = $this->createQueryBuilder('scat');
        $qb->select('scat', 'prod')
            ->join('scat.products', 'prod')
            ->where($qb->expr()->in('scat.id', $subQb->getDQL()))
            ->orderBy('scat.listOrder + 0', 'ASC');

        return $qb->getQuery()->getResult();
        
    }

        // retourne la categorie les sous categorie et les 5 derniers produits de chaque sous categorie 
        public function findAllCatsAndSubCatsLastFiveProducts(): array
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
                ->join('cat.categories', 'scat')
                ->join('scat.products', 'prod')
                ->where($qb->expr()->in('scat.id', $subQb->getDQL()))
                ->orderBy('cat.listOrder + 0', 'ASC');

            return $qb->getQuery()->getResult();
        }

    
//    /**
//     * @return SubCategory[] Returns an array of SubCategory objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SubCategory
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
