<?php

namespace App\Entity;

use App\Repository\FundRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FundRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Fund
{
    use Timestamp;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=TransactionType::class, inversedBy="funds")
     * @ORM\JoinColumn(nullable=false)
     */
    private $transactionType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $object;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="funds")
     * @ORM\JoinColumn(nullable=false)
     */
    private $manager;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransactionType(): ?TransactionType
    {
        return $this->transactionType;
    }

    public function setTransactionType(?TransactionType $transactionType): self
    {
        $this->transactionType = $transactionType;

        return $this;
    }

    public function getObject(): ?string
    {
        return $this->object;
    }

    public function setObject(string $object): self
    {
        $this->object = $object;

        return $this;
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
}
