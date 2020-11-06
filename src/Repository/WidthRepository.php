<?php

namespace App\Repository;

use App\Entity\Width;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Width|null find($id, $lockMode = null, $lockVersion = null)
 * @method Width|null findOneBy(array $criteria, array $orderBy = null)
 * @method Width[]    findAll()
 * @method Width[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WidthRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Width::class);
    }

    // /**
    //  * @return Width[] Returns an array of Width objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Width
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
