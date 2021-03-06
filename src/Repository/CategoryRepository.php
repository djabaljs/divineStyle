<?php

namespace App\Repository;

use App\Entity\Shop;
use App\Entity\Category;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
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

    // /**
    //  * @return Category[] Returns an array of Category objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Category
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getProductsQuantity()
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->leftJoin('c.product', 'p')
        ;
        
        return $qb->getQuery()->getResult();
    }

    public function getShopProductsQuantity($shop)
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->select('c')
            ->innerJoin('c.products', 'p')
            ->where('p.shop = :shop')
            ->setParameter('shop', $shop)
        ;
        
        return $qb->getQuery()->getResult();
    }
}
