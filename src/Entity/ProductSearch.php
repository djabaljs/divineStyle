<?php
namespace App\Entity;


class ProductSearch{


    private $shop;

    private $product;


    private $length;

    private $color;
  

    /**
     * Get the value of shop
     */ 
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * Set the value of shop
     *
     * @return  self
     */ 
    public function setShop($shop)
    {
        $this->shop = $shop;

        return $this;
    }

    /**
     * Get the value of product
     */ 
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set the value of product
     *
     * @return  self
     */ 
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get the value of length
     */ 
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Set the value of length
     *
     * @return  self
     */ 
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get the value of color
     */ 
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set the value of color
     *
     * @return  self
     */ 
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }
}