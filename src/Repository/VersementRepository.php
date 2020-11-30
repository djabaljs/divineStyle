<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Versement;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Versement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Versement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Versement[]    findAll()
 * @method Versement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VersementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Versement::class);
    }

    // /**
    //  * @return Versement[] Returns an array of Versement objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Versement
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findManagerVersementsByWeek(User $manager)
    {
        $start_week = date("Y-m-d 00:00:00",strtotime('monday this week'));
        $end_week = date("Y-m-d 23:59:59",strtotime('sunday this week'));


        $qb = $this->createQueryBuilder('v');
        $qb 
            ->where('v.manager = :manager')
            ->setParameter('manager', $manager)
            ->andWhere('v.createdAt >= :start')
            ->andWhere('v.createdAt <= :end')
            ->setParameter('start',$start_week)                      
            ->setParameter('end',$end_week)
            ->orderBy('v.createdAt', 'DESC')
        ;

        return $qb->getQuery()->getResult();
    }
}
