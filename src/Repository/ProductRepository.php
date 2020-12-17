<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ProductSearch;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
            ->andWhere('p.deleted = false')
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

    public function fundProductsNotDeleted()
    {
        $qb = $this->createQueryBuilder('p');
        $qb 
            ->where('p.deleted = false')
            ->innerJoin('p.category','c')
            ->andWhere('c.deleted = false')
            ->orderBy('p.createdAt', 'DESC')
            ;

        return $qb->getQuery()->getResult();
    }


    public function findProductVariations($product, $shop)
    {
      
        $qb = $this->createQueryBuilder('p');
        $qb
            ->andWhere('p.shop = :shop')
            ->setParameter('shop', $shop)
            ->andWhere('p.deleted = false')
            ->andWhere('p.id = :id')
            ->setParameter('id', $product)
            ->innerJoin('p.productVariations','v')
            ->innerJoin('v.product','vp')
            ->andWhere('vp.id = :product')
            ->setParameter('product', $product)
            ->andWhere('v.shop = :shop')
            ->setParameter('shop', $shop)
            ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function searchProducstNotVariables(ProductSearch $productSearch)
    {

        if(!is_null($productSearch->getShop()) && !is_null($productSearch->getProduct())){
           return $this->createQueryBuilder('p')
                        ->where('p.deleted = false')
                        ->andWhere('p.slug = :slug')
                        ->andWhere('p.shop = :shop')
                        ->setParameter('slug', $productSearch->getProduct()->getSlug())
                        ->setParameter('shop', $productSearch->getShop())
                        ->getQuery()
                        ->getResult()

           ;
        }elseif(is_null($productSearch->getShop()) && !is_null($productSearch->getProduct())){
            return $this->createQueryBuilder('p')
                         ->where('p.deleted = false')
                         ->andWhere('p.slug = :slug')
                         ->setParameter('slug', $productSearch->getProduct()->getSlug())
                         ->getQuery()
                         ->getResult()
            ;
         }
    }

    
    public function findProductByNameLike($name, $shop)
    {
        $qb = $this->createQueryBuilder('p');

        $qb 
            ->where('p.name LIKE :name')
            ->andWhere('p.deleted = false')
            ->andWhere('p.shop = :shop')
            ->setParameter('name', '%'.$name.'%')
            ->setParameter('shop', $shop)
            ;
        return $qb->getQuery()->getResult();
    }
}
