<?php

namespace App\Entity;

use App\Entity\Category;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProductRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("name")
 */
class Product
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
    private $name;

    /**
     * @ORM\Column(type="float")
     */
    private $buyingPrice;

      /**
     * @ORM\Column(type="float")
     */
    private $sellingPrice;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="products")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="products")
     */
    private $register;

    /**
     * @ORM\ManyToOne(targetEntity=Provider::class, inversedBy="products")
     */
    private $provider;

    /**
     * @ORM\ManyToOne(targetEntity=Shop::class, inversedBy="products")
     */
    private $shop;

    /**
     * @ORM\ManyToOne(targetEntity="Length", inversedBy="products")
     * @ORM\JoinColumn(nullable=true)
     * 
     */
    private $length;

     /**
     * @ORM\ManyToOne(targetEntity="Width", inversedBy="products")
     * @ORM\JoinColumn(nullable=true)
     */
    private $width;

    /**
     * @ORM\ManyToOne(targetEntity="Height", inversedBy="products")
     * @ORM\JoinColumn(nullable=true)
     */
    private $height;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $wcProductId;

    /**
     * @ORM\ManyToMany(targetEntity="Order", mappedBy="products")
     */
    private $orders;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $imageUrls = [];

    /**
     * @ORM\Column(type="integer")
     */
    private $minimumStock;

    /**
     * @ORM\ManyToOne(targetEntity="Color", inversedBy="products")
     */
    private $color;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

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


    public function getRegister(): ?User
    {
        return $this->register;
    }

    public function setRegister(?User $register): self
    {
        $this->register = $register;

        return $this;
    }


    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getWcProductId(): ?int
    {
        return $this->wcProductId;
    }

    public function setWcProductId(?int $wc_productId): self
    {
        $this->wcProductId = $wc_productId;

        return $this;
    }


    public function getProvider(): ?Provider
    {
        return $this->provider;
    }

    public function setProvider(?Provider $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getLength(): ?Length
    {
        return $this->length;
    }

    public function setLength(?Length $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getWidth(): ?Width
    {
        return $this->width;
    }

    public function setWidth(?Width $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?Height
    {
        return $this->height;
    }

    public function setHeight(?Height $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getBuyingPrice(): ?float
    {
        return $this->buyingPrice;
    }

    public function setBuyingPrice(float $buyingPrice): self
    {
        $this->buyingPrice = $buyingPrice;

        return $this;
    }

    public function getSellingPrice(): ?float
    {
        return $this->sellingPrice;
    }

    public function setSellingPrice(float $sellingPrice): self
    {
        $this->sellingPrice = $sellingPrice;

        return $this;
    }

    /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->addProduct($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            $order->removeProduct($this);
        }

        return $this;
    }

    public function getImageUrls(): ?array
    {
        return $this->imageUrls;
    }

    public function setImageUrls(?array $imageUrls): self
    {
        $this->imageUrls = $imageUrls;

        return $this;
    }

    public function getMinimumStock(): ?int
    {
        return $this->minimumStock;
    }

    public function setMinimumStock(int $minimumStock): self
    {
        $this->minimumStock = $minimumStock;

        return $this;
    }

    public function getColor(): ?Color
    {
        return $this->color;
    }

    public function setColor(?Color $color): self
    {
        $this->color = $color;

        return $this;
    }

}
