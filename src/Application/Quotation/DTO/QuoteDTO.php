<?php

namespace App\Application\Quotation\DTO;

final class QuoteDTO
{
    private string $provider;
    private float $price;
    private ?float $discountPrice;
    private string $currency;
    private ?string $note;

    public function __construct(
        string $provider, 
        float $price, 
        ?float $discountPrice, 
        string $currency, 
        ?string $note = null)
    {
        $this->provider = $provider;
        $this->price = $price;
        $this->discountPrice = $discountPrice;
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

    public function getDiscountPrice(): ?float
    {
        return $this->discountPrice;
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