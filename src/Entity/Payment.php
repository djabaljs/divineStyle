<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PaymentRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Payment
{
    use Timestamp;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Invoice::class, cascade={"persist", "remove"})
    * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $invoice;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $amountPaid;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity=PaymentType::class, inversedBy="payments")
     * @ORM\JoinColumn(nullable=true)
     */
    private $paymentType;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getAmountPaid(): ?float
    {
        return $this->amountPaid;
    }

    public function setAmountPaid(?float $amountPaid): self
    {
        if(is_null($amountPaid)){
            $amountPaid = 0;
        }
        $this->amountPaid = $amountPaid;

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

    public function getPaymentType(): ?PaymentType
    {
        return $this->paymentType;
    }

    public function setPaymentType(?PaymentType $paymentType): self
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }
}
