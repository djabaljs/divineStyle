<?php

namespace App\Repository;

use App\Entity\Shop;
use App\Entity\Delivery;
use App\Entity\DeliveryMan;
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
        $start_week = date("Y-m-d 00:00:00",strtotime('monday this week'));
        $end_week = date("Y-m-d 23:59:59",strtotime('sunday this week'));


        $qb = $this->createQueryBuilder('d');
        $qb 
            ->andWhere('d.deleted = FALSE')
            ->innerJoin('d.order', 'o')
            ->andWhere('o.shop = :shop')
            ->setParameter('shop', $shop)
            ->innerJoin('o.shop', 's')
            ->andWhere('s.deleted = FALSE')
            ->andWhere('d.createdAt >= :start')
            ->andWhere('d.createdAt <= :end')
            ->setParameter('start',$start_week)                      
            ->setParameter('end',$end_week)
            ->orderBy("o.createdAt", "DESC")
            ;
        return $qb->getQuery()->getResult();
    }

    public function shopOrderIsSuccessfully(Shop $shop)
    {
        $qb = $this->createQueryBuilder('d');
        $qb
            ->andWhere('d.status = TRUE')
            ->andWhere('d.deleted = FALSE')
            ->innerJoin('d.order', 'o')
            ->andWhere('o.shop = :shop')
            ->setParameter('shop', $shop)
            ->innerJoin('o.shop', 's')
            ->andWhere('s.deleted = FALSE')
            ;

        return $qb->getQuery()->getResult();
    }

    public function shopOrderIsNotSuccessfully(Shop $shop)
    {
        $qb = $this->createQueryBuilder('d');
        $qb
            ->where('d.status = FALSE')
            ->andWhere('d.deleted = FALSE')
            ->innerJoin('d.order', 'o')
            ->andWhere('o.shop = :shop')
            ->setParameter('shop', $shop)
            ->innerJoin('o.shop', 's')
            ->andWhere('s.deleted = FALSE')
            ;

        return $qb->getQuery()->getResult();
    }

    // public function deliveryManDeliveries(DeliveryMan $deliveryMan)
    // {
    //     $qb = $this->createQueryBuilder('d');
    //     $qb
    //         ->where('d.deliveryMan = :deliveryMan')
    //         ->setParameter('deliveryMan', $deliveryMan)
    //         ->le
    //         ;
    // }

    public function searchDeliveries($shop, $start, $end, $paymentType)
    {
        $start = $start->format(('Y-m-d')." 00:00:00");
        ;
        $end = $end->format(('Y-m-d')." 23:59:59");
        ;


        if(is_null($shop) && is_null($paymentType)){
            
            return $this->createQueryBuilder('d')
            ->andWhere('d.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;

        }elseif(is_null($shop) && !is_null($paymentType)){

            return $this->createQueryBuilder('d')
            ->where('d.createdAt BETWEEN :start AND :end')
            ->andWhere('d.paymentType = :paymentType')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('paymentType', $paymentType)
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;
        }

        if(is_null($paymentType)){

            return $this->createQueryBuilder('d')
            ->andWhere('d.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->innerJoin('d.order', 'o')
            ->andWhere('o.shop = :shop')
            ->setParameter('shop', $shop)
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;

        }else{

            return $this->createQueryBuilder('d')
            ->andWhere('d.createdAt BETWEEN :start AND :end')
            ->andWhere('d.paymentType = :paymentType')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('paymentType', $paymentType)
            ->innerJoin('d.order', 'o')
            ->andWhere('o.shop = :shop')
            ->setParameter('shop', $shop)
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;
        }
       

    }
}
