<?php

namespace App\Entity\Traits;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

trait Timestamps
{
    /**
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     * @var DateTimeInterface
     */
    private DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTimeInterface|null
     */
    private ?DateTimeInterface $updatedAt = null;

    /**
     * @return DateTimeInterface
     */
    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeImmutable $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTimeInterface|null $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(?DateTimeInterface $updatedAt = null): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     *
     * @return $this
     */
    public function setCreatedAtValue(): self
    {
        $this->createdAt = new DateTimeImmutable();

        return $this;
    }

    /**
     * @ORM\PreUpdate()
     *
     * @return $this
     */
    public function setUpdatedAtValue(): self
    {
        $this->updatedAt = new DateTime();

        return $this;
    }
}
