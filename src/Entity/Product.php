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
     * @ORM\ManyToOne(targetEntity=Shop::class, inversedBy="products")
     */
    private $shop;

    /**
     * @ORM\ManyToMany(targetEntity="Length", mappedBy="products", cascade={"persist"}))
     * @ORM\JoinColumn(nullable=true)
     * 
     */
    private $lengths;

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
     * @ORM\Column(type="array", nullable=true)
     */
    private $imageUrls = [];

    /**
     * @ORM\Column(type="integer")
     */
    private $minimumStock;

    /**
     * @ORM\ManyToMany(targetEntity="Color", mappedBy="products")
     */
    private $colors;


    /**
     * @ORM\Column(type="boolean")
     */
    private $isVariable;

    public $colorArrays;
    public $lengthArrays;

    /**
     * @ORM\OneToMany(targetEntity=OrderProduct::class, mappedBy="products")
     */
    private $orderProducts;

    /**
     * @ORM\OneToMany(targetEntity=ProviderProduct::class, mappedBy="product")
     */
    private $providerProducts;

    /**
     * @ORM\ManyToOne(targetEntity=Provider::class, inversedBy="products", cascade={"persist"})
     */
    private $provider;

    public function __construct()
    {
        $this->lengths = new ArrayCollection();
        $this->colors = new ArrayCollection();
        $this->orderProducts = new ArrayCollection();
        $this->providerProducts = new ArrayCollection();
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

    /**
     * @return Collection|Length[]
     */
    public function getLengths(): Collection
    {
        return $this->lengths;
    }

    public function addLength(Length $length): self
    {
        if (!$this->lengths->contains($length)) {
            $this->lengths[] = $length;
            $length->addProduct($this);
        }

        return $this;
    }

    public function removeLength(Length $length): self
    {
        if ($this->lengths->contains($length)) {
            $this->lengths->removeElement($length);
            $length->removeProduct($this);
        }

        return $this;
    }

    /**
     * @return Collection|Color[]
     */
    public function getColors(): Collection
    {
        return $this->colors;
    }

    public function addColor(Color $color): self
    {
        if (!$this->colors->contains($color)) {
            $this->colors[] = $color;
            $color->addProduct($this);
        }

        return $this;
    }

    public function removeColor(Color $color): self
    {
        if ($this->colors->contains($color)) {
            $this->colors->removeElement($color);
            $color->removeProduct($this);
        }

        return $this;
    }

    public function setColors($colors) {
       $colorArrays = [];

       foreach($colors as $color){
           $colorArrays[] = $color;
       }

        $this->colors = new ArrayCollection($colorArrays);
        // foreach($this->colors as $id => $color) {
          
        //     if(!isset($colors[$id])) {
        //         //remove from old because it doesn't exist in new
        //         $this->colors->remove($id);
        //     }
        //     else {
        //         //the product already exists do not overwrite
        //         unset($colors[$id]);
        //     }
        // }

        // //add products that exist in new but not in old
        // foreach($colors as $id => $color) {
        //     $this->color[$id] = $color;
        // }    
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


}
