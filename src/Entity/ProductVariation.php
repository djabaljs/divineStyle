<?php

namespace App\Entity;

use App\Repository\ProductVariationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductVariationRepository::class)
 */
class ProductVariation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Color::class, inversedBy="productVariations")
     */
    private $color;

    /**
     * @ORM\ManyToOne(targetEntity=Length::class, inversedBy="productVariations")
     */
    private $length;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="productVariations", cascade={"persist"})
     */
    private $product;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity=Shop::class, inversedBy="productVariations")
     */
    private $shop;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $variationId;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLength(): ?Length
    {
        return $this->length;
    }

    public function setLength(?Length $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

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

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }

    public function getVariationId(): ?int
    {
        return $this->variationId;
    }

    public function setVariationId(?int $variationId): self
    {
        $this->variationId = $variationId;

        return $this;
    }


}
