<?php

namespace App\Entity;

use App\Repository\LengthRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass=LengthRepository::class)
 * @UniqueEntity("name")
 */
class Length
{
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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="lengths")
     */
    private $register;

    /**
     * @ORM\OneToMany(targetEntity=ProductVariation::class, mappedBy="length")
     */
    private $productVariations;

        /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity=ProviderProduct::class, mappedBy="length")
     */
    private $providerProducts;

    public function __construct()
    {
        $this->productVariations = new ArrayCollection();
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


    public function getRegister(): ?User
    {
        return $this->register;
    }

    public function setRegister(?User $register): self
    {
        $this->register = $register;

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
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
            $productVariation->setLength($this);
        }

        return $this;
    }

    public function removeProductVariation(ProductVariation $productVariation): self
    {
        if ($this->productVariations->contains($productVariation)) {
            $this->productVariations->removeElement($productVariation);
            // set the owning side to null (unless already changed)
            if ($productVariation->getLength() === $this) {
                $productVariation->setLength(null);
            }
        }

        return $this;
    }


    /**
     * Get the value of slug
     */ 
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set the value of slug
     *
     * @return  self
     */ 
    public function setSlug($slug)
    {
        $this->slug = $slug;

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
            $providerProduct->setLength($this);
        }

        return $this;
    }

    public function removeProviderProduct(ProviderProduct $providerProduct): self
    {
        if ($this->providerProducts->contains($providerProduct)) {
            $this->providerProducts->removeElement($providerProduct);
            // set the owning side to null (unless already changed)
            if ($providerProduct->getLength() === $this) {
                $providerProduct->setLength(null);
            }
        }

        return $this;
    }
}
