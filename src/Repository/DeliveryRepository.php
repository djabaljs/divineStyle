<?php

namespace App\Repository;

use App\Entity\Shop;
use App\Entity\Delivery;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Delivery|null find($id, $lockMode = null, $lockVersion = null)
 * @method Delivery|null findOneBy(array $criteria, array $orderBy = null)
 * @method Delivery[]    findAll()
 * @method Delivery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Delivery::class);
    }

    // /**
    //  * @return Delivery[] Returns an array of Delivery objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Delivery
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function isSuccessFully()
    {
        $qb = $this->createQueryBuilder('d');
        $qb
            ->where('d.status IS NOT NULL')
        ;

        return $qb
                ->getQuery()
                ->getResult();
    }

    public function isNotsuccessFully()
    {
        $qb = $this->createQueryBuilder('d');
        $qb
            ->where('d.status IS NULL')
        ;

        return $qb
                ->getQuery()
                ->getResult();
    }

    public function shopDeliveries(Shop $shop)
    {
        $qb = $this->createQueryBuilder('d');
        $qb 
            ->innerJoin('d.order', 'o')
            ->where('o.shop = :shop')
            ->setParameter('shop', $shop)
            ->orderBy("o.createdAt", "DESC")
            ;
        return $qb->getQuery()->getResult();
    }

    public function shopOrderIsSuccessfully(Shop $shop)
    {
        $qb = $this->createQueryBuilder('d');
        $qb
            ->where('d.status IS NOT NULL')
            ->innerJoin('d.order', 'o')
            ->where('o.shop = :shop')
            ->setParameter('shop', $shop)
            ;

        return $qb->getQuery()->getResult();
    }

    public function shopOrderIsNotSuccessfully(Shop $shop)
    {
        $qb = $this->createQueryBuilder('d');
        $qb
            ->where('d.status IS NULL')
            ->innerJoin('d.order', 'o')
            ->where('o.shop = :shop')
            ->setParameter('shop', $shop)
            ;

        return $qb->getQuery()->getResult();
    }
}
