<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 * @ORM\HasLifecycleCallbacks()
 */
class Order
{
    use Timestamp;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $number;

    /**
     * @ORM\Column(type="float")
     */
    private $saleTotal;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $manager;

    /**
     * @ORM\ManyToOne(targetEntity=Shop::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $shop;

    /**
     * @ORM\Column(type="integer")
    */
    private $quantity;

    /**
     * @ORM\ManyToMany(targetEntity=Product::class, inversedBy="orders", cascade={"persist"})
     */
    private $products;

    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="orders")
     */
    private $customer;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $orderNumber;
    
    /**
     * @ORM\OneToOne(targetEntity="Delivery", mappedBy="order")
     */
    private $deliveries;
    
    public function __construct()
    {
        $this->products = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }


    public function setSaleTotal(float $saleTotal): self
    {
        $this->saleTotal = $saleTotal;

        return $this;
    }

    public function getManager(): ?User
    {
        return $this->manager;
    }

    public function setManager(?User $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
        }

        return $this;
    }

    public function getSaleTotal(): ?float
    {
        return $this->saleTotal;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): self
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function getDeliveries(): ?Delivery
    {
        return $this->deliveries;
    }

    public function setDeliveries(?Delivery $deliveries): self
    {
        $this->deliveries = $deliveries;

        // set (or unset) the owning side of the relation if necessary
        $newOrder = null === $deliveries ? null : $this;
        if ($deliveries->getOrder() !== $newOrder) {
            $deliveries->setOrder($newOrder);
        }

        return $this;
    }

}
