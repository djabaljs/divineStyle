<?php

namespace App\Entity;


class Billing{

    private $paymentType;

    private $deliveryMan;
    
    private $deliveryAddress;

    private $delivery;

    private $customer;

    /**
     * Get the value of paymentType
     */ 
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * Set the value of paymentType
     *
     * @return  self
     */ 
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    /**
     * Get the value of deliveryMan
     */ 
    public function getDeliveryMan()
    {
        return $this->deliveryMan;
    }

    /**
     * Set the value of deliveryMan
     *
     * @return  self
     */ 
    public function setDeliveryMan($deliveryMan)
    {
        $this->deliveryMan = $deliveryMan;

        return $this;
    }

    /**
     * Get the value of delivery
     */ 
    public function getDelivery()
    {
        return $this->delivery;
    }

    /**
     * Set the value of delivery
     *
     * @return  self
     */ 
    public function setDelivery($delivery)
    {
        $this->delivery = $delivery;

        return $this;
    }

    /**
     * Get the value of deliveryAddress
     */ 
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * Set the value of deliveryAddress
     *
     * @return  self
     */ 
    public function setDeliveryAddress($deliveryAddress)
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    /**
     * Get the value of customer
     */ 
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set the value of customer
     *
     * @return  self
     */ 
    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }
}