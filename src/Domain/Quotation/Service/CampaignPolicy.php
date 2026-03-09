<?php

namespace App\Domain\Quotation\Service;

use App\Domain\Quotation\Model\Quote;

interface CampaignPolicy
{
    public function isActive(): bool;

    /**
     * Devuelve una nueva instancia de Quote con el descuento aplicado,
     * o la misma si no hay campaña activa.
     */
    public function apply(Quote $quote): Quote;

    public function getDiscountPercentage(): float;
}