<?php

namespace App\Entity;


trait Timestamp
{
    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * Get the value of createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersit()
    {
        $this->createdAt = new \DateTime();
    }
}