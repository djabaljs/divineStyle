<?php

namespace App\Entity;

use App\Entity\Shop;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("phone")
 */
class User implements UserInterface
{
    use Timestamp;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string")
     */
    private $firstname;

    /**
     * @ORM\Column(type="string")
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToOne(targetEntity=Shop::class, mappedBy="manager")
     */
    private $shop;

    /**
     * @ORM\ManyToOne(targetEntity=Shop::class, inversedBy="staffs")
     */
    private $shops;


    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="register")
     */
    private $products;

      /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="manager")
     */
    private $orders;

    /**
     * @ORM\OneToMany(targetEntity=Category::class, mappedBy="register")
     */
    private $categories;

    /**
     * @ORM\OneToMany(targetEntity=Color::class, mappedBy="register")
     */
    private $colors;

    /**
     * @ORM\OneToMany(targetEntity=Length::class, mappedBy="register")
     */
    private $lengths;

    /**
     * @ORM\OneToMany(targetEntity=Width::class, mappedBy="register")
     */
    private $widths;

    /**
     * @ORM\OneToMany(targetEntity=Height::class, mappedBy="register")
     */
    private $heights;

    /**
     * @ORM\OneToMany(targetEntity=Attribute::class, mappedBy="register")
     */
    private $attributes;

    /**
     * @ORM\OneToMany(targetEntity=Versement::class, mappedBy="manager")
     */
    private $versements;

    /**
     * @ORM\OneToMany(targetEntity=Fund::class, mappedBy="manager")
     */
    private $funds;


    public function __construct()
    {
        $this->sales = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->colors = new ArrayCollection();
        $this->lengths = new ArrayCollection();
        $this->widths = new ArrayCollection();
        $this->heights = new ArrayCollection();
        $this->attributes = new ArrayCollection();
        $this->versements = new ArrayCollection();
        $this->funds = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * Get the value of firstname
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set the value of firstname
     *
     * @return  self
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get the value of lastname
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set the value of lastname
     *
     * @return  self
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get the value of phone
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set the value of phone
     *
     * @return  self
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get the value of address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set the value of address
     *
     * @return  self
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): self
    {
        $this->shop = $shop;

        // set (or unset) the owning side of the relation if necessary
        $newManager = null === $shop ? null : $this;
        if ($shop->getManager() !== $newManager) {
            $shop->setManager($newManager);
        }

        return $this;
    }

    public function getShops(): ?Shop
    {
        return $this->shops;
    }

    public function setShops(?Shop $shops): self
    {
        $this->shops = $shops;

        return $this;
    }


    public function __toString()
    {
        return $this->getFirstname().' '.$this->getLastname();
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
            $product->setRegister($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            // set the owning side to null (unless already changed)
            if ($product->getRegister() === $this) {
                $product->setRegister(null);
            }
        }

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
            $order->setManager($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            // set the owning side to null (unless already changed)
            if ($order->getManager() === $this) {
                $order->setManager(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->setRegister($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            // set the owning side to null (unless already changed)
            if ($category->getRegister() === $this) {
                $category->setRegister(null);
            }
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
            $color->setRegister($this);
        }

        return $this;
    }

    public function removeColor(Color $color): self
    {
        if ($this->colors->contains($color)) {
            $this->colors->removeElement($color);
            // set the owning side to null (unless already changed)
            if ($color->getRegister() === $this) {
                $color->setRegister(null);
            }
        }

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
            $length->setRegister($this);
        }

        return $this;
    }

    public function removeLength(Length $length): self
    {
        if ($this->lengths->contains($length)) {
            $this->lengths->removeElement($length);
            // set the owning side to null (unless already changed)
            if ($length->getRegister() === $this) {
                $length->setRegister(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Width[]
     */
    public function getWidths(): Collection
    {
        return $this->widths;
    }

    public function addWidth(Width $width): self
    {
        if (!$this->widths->contains($width)) {
            $this->widths[] = $width;
            $width->setRegister($this);
        }

        return $this;
    }

    public function removeWidth(Width $width): self
    {
        if ($this->widths->contains($width)) {
            $this->widths->removeElement($width);
            // set the owning side to null (unless already changed)
            if ($width->getRegister() === $this) {
                $width->setRegister(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Height[]
     */
    public function getHeights(): Collection
    {
        return $this->heights;
    }

    public function addHeight(Height $height): self
    {
        if (!$this->heights->contains($height)) {
            $this->heights[] = $height;
            $height->setRegister($this);
        }

        return $this;
    }

    public function removeHeight(Height $height): self
    {
        if ($this->heights->contains($height)) {
            $this->heights->removeElement($height);
            // set the owning side to null (unless already changed)
            if ($height->getRegister() === $this) {
                $height->setRegister(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Attribute[]
     */
    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    public function addAttribute(Attribute $attribute): self
    {
        if (!$this->attributes->contains($attribute)) {
            $this->attributes[] = $attribute;
            $attribute->setRegister($this);
        }

        return $this;
    }

    public function removeAttribute(Attribute $attribute): self
    {
        if ($this->attributes->contains($attribute)) {
            $this->attributes->removeElement($attribute);
            // set the owning side to null (unless already changed)
            if ($attribute->getRegister() === $this) {
                $attribute->setRegister(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Versement[]
     */
    public function getVersements(): Collection
    {
        return $this->versements;
    }

    public function addVersement(Versement $versement): self
    {
        if (!$this->versements->contains($versement)) {
            $this->versements[] = $versement;
            $versement->setManager($this);
        }

        return $this;
    }

    public function removeVersement(Versement $versement): self
    {
        if ($this->versements->contains($versement)) {
            $this->versements->removeElement($versement);
            // set the owning side to null (unless already changed)
            if ($versement->getManager() === $this) {
                $versement->setManager(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Fund[]
     */
    public function getFunds(): Collection
    {
        return $this->funds;
    }

    public function addFund(Fund $fund): self
    {
        if (!$this->funds->contains($fund)) {
            $this->funds[] = $fund;
            $fund->setManager($this);
        }

        return $this;
    }

    public function removeFund(Fund $fund): self
    {
        if ($this->funds->contains($fund)) {
            $this->funds->removeElement($fund);
            // set the owning side to null (unless already changed)
            if ($fund->getManager() === $this) {
                $fund->setManager(null);
            }
        }

        return $this;
    }


}