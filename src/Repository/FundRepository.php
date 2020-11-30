<?php

namespace App\Repository;

use App\Entity\Fund;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Fund|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fund|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fund[]    findAll()
 * @method Fund[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FundRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fund::class);
    }

    // /**
    //  * @return Fund[] Returns an array of Fund objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Fund
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findManagerFundByWeek(User $manager)
    {
        $start_week = date("Y-m-d 00:00:00",strtotime('monday this week'));
        $end_week = date("Y-m-d 23:59:59",strtotime('sunday this week'));


        $qb = $this->createQueryBuilder('f');
        $qb 
            ->where('f.manager = :manager')
            ->setParameter('manager', $manager)
            ->andWhere('f.createdAt >= :start')
            ->andWhere('f.createdAt <= :end')
            ->setParameter('start',$start_week)                      
            ->setParameter('end',$end_week)
            ->orderBy('f.createdAt', 'DESC');
        ;

        return $qb->getQuery()->getResult();
    }
}
