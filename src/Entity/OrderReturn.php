<?php

namespace App\Entity;

use App\Repository\OrderReturnRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderReturnRepository::class)
 * @ORM\HasLifecycleCallBacks()
 */
class OrderReturn
{
    use Timestamp;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Payment::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $firstOrder;

    /**
     * @ORM\OneToOne(targetEntity=Payment::class, cascade={"persist", "remove"})
     */
    private $lastOrder;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="orderReturns")
     * @ORM\JoinColumn(nullable=false)
     */
    private $manager;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

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

    public function getFirstOrder(): ?Payment
    {
        return $this->firstOrder;
    }

    public function setFirstOrder(Payment $firstOrder): self
    {
        $this->firstOrder = $firstOrder;

        return $this;
    }

    public function getLastOrder(): ?Payment
    {
        return $this->lastOrder;
    }

    public function setLastOrder(?Payment $lastOrder): self
    {
        $this->lastOrder = $lastOrder;

        return $this;
    }
}
