<?php

namespace App\Services\Product;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductService{

    /**
     * @var $products
     */
    protected $productsMap;
    
    /**
     * @var $productRepository
     */
    protected $productRepository;

    /**
     * @var $manager
     */
    protected $manager;

    /**
     * @method construct
     */
    public function __construct(ProductRepository $productRepository, EntityManagerInterface $manager)
    {
        $this->productRepository = $productRepository;
        $this->manager = $manager;
    }
    

    public function map(Array $array)
    {

        $this->manager->getConnection()->beginTransaction();

        try{

            foreach($array as $key => $value){
          
             $this->productsMap[$key] = new Product(
                    $value['id'], 
                    $value['name'], 
                    $value['price'], 
                    $value['categories'],
                    $value['dimensions'], 
                    $value['stock_quantity'],
                    $value['images']
                );

            }
    
            $products =$this->manager->getRepository(Product::class)->findAll();

            $inside = [];
            $notInside = [];
            
            if(!empty($products)){

                $es = [];

                    foreach($this->productsMap as $key => $value){
                        $es[$key] = $value->getWoocommerceId();
                    }
    
                    foreach($products as $key => $value){

                        if(in_array($value->getWoocommerceId(), $es)){

                            $inside[$key] = $value;

                            $product = $this->manager->getRepository(Product::class)->findOneBy(['woocommerceId' => $value->getWoocommerceId()]);
                            dd($this->productsMap[$key]->getPrice());
                            $product->setName($this->productsMap[$key]->getName());
                            $product->setPrice($this->productsMap[$key]->getPrice());
                            $product->setCategories($value['categories']);
                            $product->setDimensions($this->productsMap[$key]->getDimensions());
                            $product->setQuantity($this->productsMap[$key]->getStockQuantity());
                            $product->setImagesUrl($this->productsMap[$key]->getImagesUrl());
                            
                            $this->manager->persist($product);

                        }else{
                            $notInside[$key] = $value;
                        }
                    }
            }else{

                foreach($this->productsMap as $key => $value){
                    $inside[$key] = $value;
                }
            }
           
                 
            $this->manager->getConnection()->beginTransaction();
    
            foreach($notInside as $key => $value){
                $this->manager->remove($value);
            }
    
            foreach($inside as $key => $value){
                $this->manager->persist($value);
            }
    
            $this->manager->flush();
            $this->manager->commit();

        }catch(\Exception $e){
            $this->manager->rollback();
            throw $e;
        }

        return $inside;

    }


}