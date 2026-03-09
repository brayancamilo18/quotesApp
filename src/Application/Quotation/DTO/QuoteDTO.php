<?php

namespace App\Application\Quotation\DTO;

final class QuoteDTO
{
    private string $provider;
    private float $price;
    private ?float $discountedPrice;
    private string $currency;
    private ?string $note;

    public function __construct(
        string $provider, 
        float $price, 
        ?float $discountedPrice, 
        string $currency, 
        ?string $note = null)
    {
        $this->provider = $provider;
        $this->price = $price;
        $this->discountedPrice = $discountedPrice;
        $this->currency = $currency;
        $this->note = $note;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getDiscountedPrice(): ?float
    {
        return $this->discountedPrice;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }
}