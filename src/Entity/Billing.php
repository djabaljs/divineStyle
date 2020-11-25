<?php

namespace App\Entity;


class Billing{

    private $paymentType;

    private $amountPaid;

    private $discount;

    private $deliveryMan;
    
    private $deliveryAddress;

    private $deliveryAmount;

    private $delivery;

    private $customer;

    private $recipient;

    private $recipientPhone;

    private $choice;

    private $customerType;
    private $customerFistname;
    private $customerLastname;
    private $customerPhone;
    private $customerEmail;
    private $customerBirthDay;

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

    /**
     * Get the value of amountPaid
     */ 
    public function getAmountPaid()
    {
        return $this->amountPaid;
    }

    /**
     * Set the value of amountPaid
     *
     * @return  self
     */ 
    public function setAmountPaid($amountPaid)
    {
        $this->amountPaid = $amountPaid;

        return $this;
    }

    /**
     * Get the value of deliveryAmount
     */ 
    public function getDeliveryAmount()
    {
        return $this->deliveryAmount;
    }

    /**
     * Set the value of deliveryAmount
     *
     * @return  self
     */ 
    public function setDeliveryAmount($deliveryAmount)
    {
        $this->deliveryAmount = $deliveryAmount;

        return $this;
    }

    /**
     * Get the value of recipient
     */ 
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * Set the value of recipient
     *
     * @return  self
     */ 
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Get the value of recipientPhone
     */ 
    public function getRecipientPhone()
    {
        return $this->recipientPhone;
    }

    /**
     * Set the value of recipientPhone
     *
     * @return  self
     */ 
    public function setRecipientPhone($recipientPhone)
    {
        $this->recipientPhone = $recipientPhone;

        return $this;
    }

    /**
     * Get the value of choice
     */ 
    public function getChoice()
    {
        return $this->choice;
    }

    /**
     * Set the value of choice
     *
     * @return  self
     */ 
    public function setChoice($choice)
    {
        $this->choice = $choice;

        return $this;
    }

    /**
     * Get the value of discount
     */ 
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set the value of discount
     *
     * @return  self
     */ 
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Get the value of customerFistname
     */ 
    public function getCustomerFistname()
    {
        return $this->customerFistname;
    }

    /**
     * Set the value of customerFistname
     *
     * @return  self
     */ 
    public function setCustomerFistname($customerFistname)
    {
        $this->customerFistname = $customerFistname;

        return $this;
    }

    /**
     * Get the value of clientType
     */ 
    public function getClientType()
    {
        return $this->clientType;
    }

    /**
     * Set the value of clientType
     *
     * @return  self
     */ 
    public function setClientType($clientType)
    {
        $this->clientType = $clientType;

        return $this;
    }
    /**
     * Get the value of customerPhone
     */ 
    public function getCustomerPhone()
    {
        return $this->customerPhone;
    }

    /**
     * Set the value of customerPhone
     *
     * @return  self
     */ 
    public function setCustomerPhone($customerPhone)
    {
        $this->customerPhone = $customerPhone;

        return $this;
    }

    /**
     * Get the value of customerEmail
     */ 
    public function getCustomerEmail()
    {
        return $this->customerEmail;
    }

    /**
     * Set the value of customerEmail
     *
     * @return  self
     */ 
    public function setCustomerEmail($customerEmail)
    {
        $this->customerEmail = $customerEmail;

        return $this;
    }

    /**
     * Get the value of customerBirthDay
     */ 
    public function getCustomerBirthDay()
    {
        return $this->customerBirthDay;
    }

    /**
     * Set the value of customerBirthDay
     *
     * @return  self
     */ 
    public function setCustomerBirthDay($customerBirthDay)
    {
        $this->customerBirthDay = $customerBirthDay;

        return $this;
    }

    /**
     * Get the value of customerType
     */ 
    public function getCustomerType()
    {
        return $this->customerType;
    }

    /**
     * Set the value of customerType
     *
     * @return  self
     */ 
    public function setCustomerType($customerType)
    {
        $this->customerType = $customerType;

        return $this;
    }

    /**
     * Get the value of customerLastname
     */ 
    public function getCustomerLastname()
    {
        return $this->customerLastname;
    }

    /**
     * Set the value of customerLastname
     *
     * @return  self
     */ 
    public function setCustomerLastname($customerLastname)
    {
        $this->customerLastname = $customerLastname;

        return $this;
    }
}