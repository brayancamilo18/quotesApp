<?php

namespace App\Application\Quotation\DTO;

final class CalculatedQuotesResponse
{
    private bool $campaignActive;

    private array $offers;

    public function __construct(bool $campaignActive, array $offers)
    {
        $this->campaignActive = $campaignActive;
        $this->offers = $offers;
    }

    public function isCampaignActive(): bool
    {
        return $this->campaignActive;
    }

    public function getOffers(): array
    {
        return $this->offers;
    }
}
