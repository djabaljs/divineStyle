<?php

namespace App\Repository;

use App\Entity\Shop;
use App\Entity\Payment;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Payment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Payment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Payment[]    findAll()
 * @method Payment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    // /**
    //  * @return Payment[] Returns an array of Payment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Payment
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function ordersSuccessfully()
    {
        $qb = $this->createQueryBuilder('p');
        $qb 
            ->where('p.amountPaid != 0')
            ->orderBy('p.createdAt', 'DESC')
            ;

        return $qb->getQuery()
                  ->getResult();
    }

    public function shopOrdersLastFiveSuccessfully(Shop $shop)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->innerJoin('p.invoice', 'i')
            ->innerJoin('i.orders', 'o')
            ->where('o.shop = :shop')
            ->orderBy('o.createdAt', 'DESC')
            ->setParameter('shop', $shop)
            ->setMaxResults(5)
            ;
        return $qb->getQuery()
                  ->getResult();
    }

    public function ordersLastFiveSuccessfully()
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->innerJoin('p.invoice', 'i')
            ->innerJoin('i.orders', 'o')
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults(5)
            ;
        return $qb->getQuery()
                  ->getResult();
    }


    public function ordersNotSuccessfully()
    {
        $qb = $this->createQueryBuilder('p');
        $qb 
            ->where('p.amountPaid = 0')
            ->orderBy('p.createdAt', 'DESC')
            ;

        return $qb
                ->getQuery()
                ->getResult();
    }

    public function shopPayments(Shop $shop)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->innerJoin('p.invoice', 'i')
            ->innerJoin('i.orders', 'o')
            ->where('o.shop = :shop')
            ->setParameter('shop', $shop);
            ;

        return $qb->getQuery()->getResult();
    }

    public function shopOrdersSuccessfully($shop)
    {
        $qb = $this->createQueryBuilder('p');
        $qb 
            ->where('p.amountPaid != 0')
            ->innerJoin('p.invoice', 'i')
            ->innerJoin('i.orders', 'o')
            ->where('o.shop = :shop')
            ->setParameter('shop', $shop)
            ->orderBy('p.createdAt', 'DESC')
            ;

        return $qb
                ->getQuery()
                ->getResult();
    }

    public function shopOrdersNotSuccessfully($shop)
    {
        $qb = $this->createQueryBuilder('p');
        $qb 
            ->where('p.amountPaid != 0')
            ->innerJoin('p.invoice', 'i')
            ->innerJoin('i.orders', 'o')
            ->where('o.shop = :shop')
            ->setParameter('shop', $shop)
            ->orderBy('p.createdAt', 'DESC')
            ;

        return $qb
                ->getQuery()
                ->getResult();
    }

    public function findPaymentsByWeek()
    {
        $start_week = date("Y-m-d",strtotime('monday this week'));
        $end_week = date("Y-m-d",strtotime('saturday this week'));
        return $this->createQueryBuilder('p')
                    ->innerJoin('p.invoice', 'i')
                    ->innerJoin('i.orders', 'o')
                    ->where('p.createdAt >= :start')
                    ->andWhere('p.createdAt <= :end')
                    ->setParameter('start',$start_week)                      
                    ->setParameter('end',$end_week)
                    ->getQuery()
                    ->getResult(); 
                ;
        
    }

    public function searchPayments($shop, $start, $end, $paymentType)
    {
        $start = $start->format(('Y-m-d')." 00:00:00");
        ;
        $end = $end->format(('Y-m-d')." 23:59:59");
        ;
        // dd($start, $end);
        return $this->createQueryBuilder('p')
                    ->where('p.createdAt BETWEEN :start AND :end')
                    ->andWhere('p.paymentType = :paymentType')
                    ->setParameter('start', $start)
                    ->setParameter('end', $end)
                    ->setParameter('paymentType', $paymentType)
                    ->innerJoin('p.invoice', 'i')
                    ->innerJoin('i.orders', 'o')
                    ->andWhere('o.shop = :shop')
                    ->setParameter('shop', $shop)
                    ->orderBy('p.createdAt', 'DESC')
                    ->getQuery()
                    ->getResult()
                    ;

    }
}
