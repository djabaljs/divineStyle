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
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="products")
     */
    private $register;


    /**
     * @ORM\ManyToOne(targetEntity=Shop::class, inversedBy="products")
     */
    private $shop;


    private $lengths;

     /**
     * @ORM\ManyToOne(targetEntity="Width", inversedBy="products")
     * @ORM\JoinColumn(nullable=true)
     */
    // private $width;

    /**
     * @ORM\ManyToOne(targetEntity="Height", inversedBy="products")
     * @ORM\JoinColumn(nullable=true)
     */
    // private $height;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $wcProductId;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $imageUrls = [];

    /**
     * @ORM\Column(type="integer")
     */
    private $minimumStock;

    private $colors;


    /**
     * @ORM\Column(type="boolean")
     */
    private $isVariable;

    public $colorArrays = [];
    public $lengthArrays = [];

    /**
     * @ORM\OneToMany(targetEntity=OrderProduct::class, mappedBy="products", cascade={"persist","remove"}, orphanRemoval=true)
     */
    private $orderProducts;

    /**
     * @ORM\OneToMany(targetEntity=ProviderProduct::class, mappedBy="product")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $providerProducts;

    /**
     * @ORM\ManyToOne(targetEntity=Provider::class, inversedBy="products", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $provider;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $onSaleAmount;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;

    /**
     * @ORM\OneToMany(targetEntity=ProductVariation::class, mappedBy="product")
     */
    private $productVariations;

    public function __construct()
    {
        $this->lengths = new ArrayCollection();
        $this->colors = new ArrayCollection();
        $this->orderProducts = new ArrayCollection();
        $this->providerProducts = new ArrayCollection();
        $this->productVariations = new ArrayCollection();
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

    // public function getWidth(): ?Width
    // {
    //     return $this->width;
    // }

    // public function setWidth(?Width $width): self
    // {
    //     $this->width = $width;

    //     return $this;
    // }

    // public function getHeight(): ?Height
    // {
    //     return $this->height;
    // }

    // public function setHeight(?Height $height): self
    // {
    //     $this->height = $height;

    //     return $this;
    // }

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

    public function getIsVariable(): ?bool
    {
        return $this->isVariable;
    }

    public function setIsVariable(bool $isVariable): self
    {
        $this->isVariable = $isVariable;

        return $this;
    }

   

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;

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
            $orderProduct->setProducts($this);
        }

        return $this;
    }

    public function removeOrderProduct(OrderProduct $orderProduct): self
    {
        if ($this->orderProducts->contains($orderProduct)) {
            $this->orderProducts->removeElement($orderProduct);
            // set the owning side to null (unless already changed)
            if ($orderProduct->getProducts() === $this) {
                $orderProduct->setProducts(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProviderProduct[]
     */
    public function getProviderProducts(): Collection
    {
        return $this->providerProducts;
    }

    public function addProviderProduct(ProviderProduct $providerProduct): self
    {
        if (!$this->providerProducts->contains($providerProduct)) {
            $this->providerProducts[] = $providerProduct;
            $providerProduct->setProduct($this);
        }

        return $this;
    }

    public function removeProviderProduct(ProviderProduct $providerProduct): self
    {
        if ($this->providerProducts->contains($providerProduct)) {
            $this->providerProducts->removeElement($providerProduct);
            // set the owning side to null (unless already changed)
            if ($providerProduct->getProduct() === $this) {
                $providerProduct->setProduct(null);
            }
        }

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

    public function __toString()
    {
        return $this->getName();
    }

    public function getOnSaleAmount(): ?float
    {
        return $this->onSaleAmount;
    }

    public function setOnSaleAmount(?float $onSaleAmount): self
    {
        $this->onSaleAmount = $onSaleAmount;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return Collection|ProductVariation[]
     */
    public function getProductVariations(): Collection
    {
        return $this->productVariations;
    }

    public function addProductVariation(ProductVariation $productVariation): self
    {
        if (!$this->productVariations->contains($productVariation)) {
            $this->productVariations[] = $productVariation;
            $productVariation->setProduct($this);
        }

        return $this;
    }

    public function removeProductVariation(ProductVariation $productVariation): self
    {
        if ($this->productVariations->contains($productVariation)) {
            $this->productVariations->removeElement($productVariation);
            // set the owning side to null (unless already changed)
            if ($productVariation->getProduct() === $this) {
                $productVariation->setProduct(null);
            }
        }

        return $this;
    }


    /**
     * Get the value of lengths
     */ 
    public function getLengths()
    {
        return $this->lengths;
    }

    /**
     * Set the value of lengths
     *
     * @return  self
     */ 
    public function setLengths($lengths)
    {
        $this->lengths = $lengths;

        return $this;
    }

    /**
     * Get the value of colors
     */ 
    public function getColors()
    {
        return $this->colors;
    }

    /**
     * Set the value of colors
     *
     * @return  self
     */ 
    public function setColors($colors)
    {
        $this->colors = $colors;

        return $this;
    }
}
