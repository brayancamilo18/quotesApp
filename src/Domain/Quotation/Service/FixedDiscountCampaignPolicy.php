<?php

namespace App\Domain\Quotation\Service;

use App\Domain\Quotation\Model\Quote;
use App\Domain\Quotation\Model\Money;

final class FixedDiscountCampaignPolicy implements CampaignPolicy
{
    private bool $active;
    private float $discountPercentage;

    public function __construct(bool $active, float $discountPercentage = 0.05)
    {
        $this->active = $active;
        $this->discountPercentage = $discountPercentage;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getDiscountPercentage(): float
    {
        return $this->discountPercentage;
    }

    public function apply(Quote $quote): Quote
    {
        if (!$this->isActive()) {
            return $quote;
        }

        $base = $quote->getBasePrice();
        $discountedAmount = $base->getAmount() * (1 - $this->discountPercentage);

        $discountedMoney = new Money($discountedAmount, $base->getCurrency());

        return $quote->withDiscountedPrice($discountedMoney);
    }
}
