<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    // /**
    //  * @return Product[] Returns an array of Product objects
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
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findProductByName($name)
    {
        $qb = $this->createQueryBuilder('p');

        $qb 
            ->where('p.name LIKE :name')
            ->setParameter('name', '%'.$name.'%')
            ;
        return $qb->getQuery()->getResult();
    }

    public function findProducts()
    {
        $qb = $this->createQueryBuilder('p');

        $qb 
            ->select('p.name')
            ->distinct()
            ;
        return $qb->getQuery()->getResult();
    }



    public function productsDistinct()
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->orderBy('p.createdAt', 'DESC')
        ;
        
       $products =  $qb->getQuery()->getResult();

       $sameName = [];
       $sameProduct = [];
       foreach($products as $product){
           if(!in_array($product->getName(), $sameName)){
               $sameName[] = $product->getName();
               $sameProduct[] = $product;
           }
       }

       return $sameProduct;
    }
}
