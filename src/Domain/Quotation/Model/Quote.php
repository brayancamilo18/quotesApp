<?php

namespace App\Domain\Quotation\Model;

use App\Domain\Shared\Money;

final class Quote
{
    private string $providerName;
    private Money $basePrice;
    private ?Money $discountedPrice;
    private ?string $note;

    public function __construct(
        string $providerName,
        Money $basePrice,
        ?Money $discountedPrice = null,
        ?string $note = null
    )
    {
        $this->providerName = $providerName;
        $this->basePrice = $basePrice;
        $this->discountedPrice = $discountedPrice;
        $this->note = $note;
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function getBasePrice(): Money
    {
        return $this->basePrice;
    }

    public function getDiscountedPrice(): ?Money
    {
        return $this->discountedPrice;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function withDiscountedPrice(Money $discountedPrice): self
    {
        return new self(
            $this->providerName,
            $this->basePrice,
            $discountedPrice,
            $this->note
        );
    }

    public function withNote(string $note): self
    {
        return new self(
            $this->providerName,
            $this->basePrice,
            $this->discountedPrice,
            $note
        );
    }

    /**
     * Precio efectivo que verá el cliente (descontado si existe, base en caso contrario)
     */
    public function getEffectivePrice(): Money
    {
        return $this->discountedPrice ?? $this->basePrice;
    }
}