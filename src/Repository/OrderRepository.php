<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    // /**
    //  * @return Order[] Returns an array of Order objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Order
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    // public function findProducts(Order $order)
    // {
    //     $qb = $this->createQueryBuilder('o');
    //     $qb 
    //         ->innerJoin('o.orderProducts','op')
    //         ->select('op.products')
    //         ->andWhere('op.productOrder = :order')
    //         ->setParameter('order', $order)
    //         ;
    //     return $qb->getQuery()->getResult();
    // }

    public function findLastFiveProducts()
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.createdAt','ASC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
            ;
    }
}
