<?php

namespace App\Repository;

use App\Entity\Length;
use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Length|null find($id, $lockMode = null, $lockVersion = null)
 * @method Length|null findOneBy(array $criteria, array $orderBy = null)
 * @method Length[]    findAll()
 * @method Length[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LengthRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Length::class);
    }

    // /**
    //  * @return Length[] Returns an array of Length objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Length
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getProductLengths(Product $product)
    {
        $qb = $this->createQueryBuilder('l');
        $qb 
            ->innerJoin('l.products', 'p')
            ->where('p = :product')
            ->setParameter('product', $product)
            ;

        return $qb->getQuery()->getResult();
    }
}
