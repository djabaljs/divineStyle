<?php

namespace App\Entity;

use App\Repository\AttributeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AttributeRepository::class)
 */
class Attribute
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
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @ORM\Column(type="boolean")
     */
    private $visible;

    /**
     * @ORM\Column(type="boolean")
     */
    private $variation;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="attributes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $register;

    /**
     * @ORM\Column(type="array")
     */
    private $options = [];


    public $text;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }

    public function getVariation(): ?bool
    {
        return $this->variation;
    }

    public function setVariation(bool $variation): self
    {
        $this->variation = $variation;

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

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }


    /**
     * Get the value of text
     */ 
    public function getText()
    {
        return $this->text;
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
}
