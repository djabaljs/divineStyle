<?php

namespace App\Entity;


class ShopQuantity{

    private $shopName;

    private $quantity;

    /**
     * Get the value of shopName
     */ 
    public function getShopName()
    {
        return $this->shopName;
    }

    /**
     * Set the value of shopName
     *
     * @return  self
     */ 
    public function setShopName($shopName)
    {
        $this->shopName = $shopName;

        return $this;
    }

    /**
     * Get the value of quantity
     */ 
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set the value of quantity
     *
     * @return  self
     */ 
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }
}