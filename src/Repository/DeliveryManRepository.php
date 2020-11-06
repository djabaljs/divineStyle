<?php

namespace App\Repository;

use App\Entity\DeliveryMan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeliveryMan|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeliveryMan|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeliveryMan[]    findAll()
 * @method DeliveryMan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryManRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliveryMan::class);
    }

    // /**
    //  * @return DeliveryMan[] Returns an array of DeliveryMan objects
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
    public function findOneBySomeField($value): ?DeliveryMan
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
