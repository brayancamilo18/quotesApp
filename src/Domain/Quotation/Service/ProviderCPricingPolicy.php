<?php

namespace App\Domain\Quotation\Service;

use App\Domain\Quotation\Model\Money;
use App\Domain\Quotation\Model\QuoteRequest;

final class ProviderCPricingPolicy
{
    private const BASE_PRICE = 230.0;

    public function calculatePrice(QuoteRequest $request): Money
    {
        $driver = $request->getDriver();
        $car = $request->getCar();

        $basePrice = self::BASE_PRICE;


        $age = $driver->getAge();
        if ($age < 25) {
            $basePrice += 80;
        } elseif ($age <= 45) {
            $basePrice += 20;
        } else {
            $basePrice += 60;
        }

        switch ($car->getType()) {
            case 'suv':
                $basePrice += 150;
                break;
            case 'turismo':
                $basePrice += 40;
                break;
            case 'compacto':
            default:
                $basePrice += 0;
                break;
        }

        // Uso del coche
        if ($car->isCommercialUse()) {
            $basePrice *= 1.10; // +10%
        }

        return new Money($basePrice, 'EUR');
    }
}
