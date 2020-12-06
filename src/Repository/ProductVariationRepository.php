<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ProductSearch;
use App\Entity\ProductVariation;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method ProductVariation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductVariation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductVariation[]    findAll()
 * @method ProductVariation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductVariationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductVariation::class);
    }

    // /**
    //  * @return ProductVariation[] Returns an array of ProductVariation objects
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
    public function findOneBySomeField($value): ?ProductVariation
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findProducts()
    {
        $qb = $this->createQueryBuilder('v');
        $qb
            ->innerJoin('v.product', 'p')
            ->where('p.deleted = false')
            ->orderBy('p.createdAt', 'DESC')
        ;

        return $qb->getQuery()->getResult();
    }


    public function findProductsVariation(Product $product){
        return $this->createQueryBuilder('v')
                    ->innerJoin('v.product','p')
                    ->where('p.slug = :slug')
                    ->setParameter('slug', $product->getSlug())
                    ->getQuery()
                    ->getResult()
        ;
    }

    public function searchProducts(ProductSearch $productSearch)
    {

/**S1 */ if(!is_null($productSearch->getShop()) && !is_null($productSearch->getProduct()) && !is_null($productSearch->getLength()) && !is_null($productSearch->getColor())){
            $qb = $this->createQueryBuilder('v');
            $qb
                ->andWhere('v.color = :color')
                ->andWhere('v.length = :length')
                ->andWhere('v.shop = :shop')
                ->innerJoin('v.product', 'p')
                ->andWhere('p.slug = :slug')
                ->setParameter('color', $productSearch->getColor())
                ->setParameter('length', $productSearch->getLength())
                ->setParameter('shop', $productSearch->getShop())
                ->setParameter('slug', $productSearch->getProduct()->getSlug())
                ;

            return $qb->getQuery()->getResult();
        /**S2 */}elseif(!is_null($productSearch->getShop()) && !is_null($productSearch->getProduct()) && is_null($productSearch->getLength()) && is_null($productSearch->getColor())){

            $qb = $this->createQueryBuilder('v');
            $qb
                ->andWhere('v.shop = :shop')
                ->innerJoin('v.product', 'p')
                ->andWhere('p.slug = :slug')
                ->setParameter('shop', $productSearch->getShop())
                ->setParameter('slug', $productSearch->getProduct()->getSlug())

                ;

            return $qb->getQuery()->getResult();
       /**S3 */ }elseif(!is_null($productSearch->getShop()) && is_null($productSearch->getProduct()) && is_null($productSearch->getLength()) && is_null($productSearch->getColor())){

            $qb = $this->createQueryBuilder('v');
            $qb
                ->andWhere('v.shop = :shop')
                ->setParameter('shop', $productSearch->getShop())
                ;

            return $qb->getQuery()->getResult();
       /**S3 */ }elseif(!is_null($productSearch->getShop()) && is_null($productSearch->getProduct()) && !is_null($productSearch->getLength()) && !is_null($productSearch->getColor())){

            $qb = $this->createQueryBuilder('v');
            $qb
                ->andWhere('v.color = :color')
                ->andWhere('v.length = :length')
                ->andWhere('v.shop = :shop')
                ->setParameter('color', $productSearch->getColor())
                ->setParameter('length', $productSearch->getLength())
                ->setParameter('shop', $productSearch->getShop())

                ;

            return $qb->getQuery()->getResult();
       /**S3 */ }elseif(!is_null($productSearch->getShop()) && is_null($productSearch->getProduct()) && is_null($productSearch->getLength()) && !is_null($productSearch->getColor())){

            $qb = $this->createQueryBuilder('v');
            $qb
                ->andWhere('v.color = :color')
                ->andWhere('v.shop = :shop')
                ->setParameter('color', $productSearch->getColor())
                ->setParameter('shop', $productSearch->getShop())
                ;

            return $qb->getQuery()->getResult();
       /**S4 */ }elseif(!is_null($productSearch->getShop()) && is_null($productSearch->getProduct()) && is_null($productSearch->getLength()) && is_null($productSearch->getColor())){

            $qb = $this->createQueryBuilder('v');
            $qb
                ->andWhere('v.shop = :shop')
                ->setParameter('shop', $productSearch->getShop())
                ;

            return $qb->getQuery()->getResult();
    /**P1 */ }elseif(is_null($productSearch->getShop()) && !is_null($productSearch->getProduct()) && !is_null($productSearch->getLength()) && !is_null($productSearch->getColor())){
                $qb = $this->createQueryBuilder('v');
                $qb
                    ->andWhere('v.color = :color')
                    ->andWhere('v.length = :length')
                    ->innerJoin('v.product', 'p')
                    ->andWhere('p.slug = :slug')
                    ->setParameter('color', $productSearch->getColor())
                    ->setParameter('length', $productSearch->getLength())
                    ->setParameter('slug', $productSearch->getProduct()->getSlug())
                    ;
    
                return $qb->getQuery()->getResult();
    /**P2 */}elseif(is_null($productSearch->getShop()) && !is_null($productSearch->getProduct()) && is_null($productSearch->getLength()) && !is_null($productSearch->getColor())){
    
                $qb = $this->createQueryBuilder('v');
                $qb
                    ->andWhere('v.color = :color')
                    ->innerJoin('v.product', 'p')
                    ->andWhere('p.slug = :slug')
                    ->setParameter('color', $productSearch->getColor())
                    ->setParameter('slug', $productSearch->getProduct()->getSlug())
        
                    ;
    
                return $qb->getQuery()->getResult();
    /**P3 */ }elseif(is_null($productSearch->getShop()) && !is_null($productSearch->getProduct()) && is_null($productSearch->getLength()) && is_null($productSearch->getColor())){
    
                    $qb = $this->createQueryBuilder('v');
                    $qb
                        ->innerJoin('v.product', 'p')
                        ->andWhere('p.slug = :slug')
                        ->setParameter('slug', $productSearch->getProduct()->getSlug())
                    ;
    
                return $qb->getQuery()->getResult();
    /**L1 */ }elseif(is_null($productSearch->getShop()) && !is_null($productSearch->getProduct()) && !is_null($productSearch->getLength()) && !is_null($productSearch->getColor())){
    
                $qb = $this->createQueryBuilder('v');
                $qb
                        ->andWhere('v.color = :color')
                        ->andWhere('v.length = :length')
                        ->innerJoin('v.product', 'p')
                        ->andWhere('p.slug = :slug')
                        ->setParameter('color', $productSearch->getColor())
                        ->setParameter('length', $productSearch->getLength())
                        ->setParameter('slug', $productSearch->getProduct()->getSlug())
                    ;
    
                return $qb->getQuery()->getResult();
          /**L2 */  }elseif(is_null($productSearch->getShop()) && is_null($productSearch->getProduct()) && !is_null($productSearch->getLength()) && !is_null($productSearch->getColor())){

            // dd($productSearch->getLength(), $productSearch->getColor());
                $qb = $this->createQueryBuilder('v');
                $qb
                        ->where('v.color = :color')
                        ->andWhere('v.length = :length')
                        ->setParameter('color', $productSearch->getColor())
                        ->setParameter('length', $productSearch->getLength())
                    ;
    
                    return $qb->getQuery()->getResult();
        /**L3 */  }elseif(is_null($productSearch->getShop()) && is_null($productSearch->getProduct()) && !is_null($productSearch->getLength()) && is_null($productSearch->getColor())){
    
                $qb = $this->createQueryBuilder('v');
                $qb
                        ->andWhere('v.length = :length')
                        ->setParameter('length', $productSearch->getLength())
                    ;
    
                return $qb->getQuery()->getResult();
        /**C1 */  }elseif(is_null($productSearch->getShop()) && is_null($productSearch->getProduct()) && is_null($productSearch->getLength()) && !is_null($productSearch->getColor())){
    
                $qb = $this->createQueryBuilder('v');
                $qb
                        ->andWhere('v.color = :color')
                        ->setParameter('color', $productSearch->getColor())
                    ;
    
                return $qb->getQuery()->getResult();
            }elseif(is_null($productSearch->getShop()) && is_null($productSearch->getProduct()) && is_null($productSearch->getLength()) && is_null($productSearch->getColor())){
    
                $qb = $this->createQueryBuilder('v');
    
                return $qb->getQuery()->getResult();
            }

        

    }

    public function findProductVariation($color, $length, $product)
    {
        return  $this->createQueryBuilder('v')
                     ->innerJoin('v.color','c')
                     ->andWhere('c.name LIKE :color')
                     ->setParameter('color', '%'.$color.'%')
                     ->innerJoin('v.length','l')
                     ->andWhere('l.name LIKE :length')
                     ->setParameter('length', '%'.$length.'%')
                     ->innerJoin('v.product','p')
                     ->andWhere('p.wcProductId = :id')
                     ->setParameter('id', $product->getWcProductId())
                     ->getQuery()
                     ->getResult()
        ;

    }
}
