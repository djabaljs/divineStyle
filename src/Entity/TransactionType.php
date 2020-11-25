<?php

namespace App\Entity;

use App\Repository\TransactionTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TransactionTypeRepository::class)
 */
class TransactionType
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
     * @ORM\OneToMany(targetEntity=Fund::class, mappedBy="transactionType")
     */
    private $funds;

    public function __construct()
    {
        $this->funds = new ArrayCollection();
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
            $fund->setTransactionType($this);
        }

        return $this;
    }

    public function removeFund(Fund $fund): self
    {
        if ($this->funds->contains($fund)) {
            $this->funds->removeElement($fund);
            // set the owning side to null (unless already changed)
            if ($fund->getTransactionType() === $this) {
                $fund->setTransactionType(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
