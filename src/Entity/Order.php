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
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="orders")
     */
    private $customer;

    /**
     * @ORM\OneToOne(targetEntity="Delivery", mappedBy="order")
     */
    private $deliveries;

    /**
     * @ORM\OneToMany(targetEntity=OrderProduct::class, mappedBy="productOrder", cascade={"persist"})
     */
    private $orderProducts;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $orderNumber;

    public function __construct()
    {
        $this->orderProducts = new ArrayCollection();
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

    public function getSaleTotal(): ?float
    {
        return $this->saleTotal;
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


    /**
     * @return Collection|OrderProduct[]
     */
    public function getOrderProducts(): Collection
    {
        return $this->orderProducts;
    }

    public function addOrderProduct(OrderProduct $orderProduct): self
    {
        if (!$this->orderProducts->contains($orderProduct)) {
            $this->orderProducts[] = $orderProduct;
            $orderProduct->setProductOrder($this);
        }

        return $this;
    }

    public function removeOrderProduct(OrderProduct $orderProduct): self
    {
        if ($this->orderProducts->contains($orderProduct)) {
            $this->orderProducts->removeElement($orderProduct);
            // set the owning side to null (unless already changed)
            if ($orderProduct->getProductOrder() === $this) {
                $orderProduct->setProductOrder(null);
            }
        }

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

}
