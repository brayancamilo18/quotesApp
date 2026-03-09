<?php

namespace App\Domain\Quotation\Service;

use App\Domain\Quotation\Model\Car;
use App\Domain\Quotation\Model\QuoteRequest;
use App\Domain\Quotation\Model\Money;

final class ProviderBPricingPolicy
{
    private const BASE_PRICE = 250.0;

    public function calculatePrice(QuoteRequest $request): Money
    {
        $driver = $request->getDriver();
        $car = $request->getCar();

        $price = self::BASE_PRICE;

        // Edad
        $age = $driver->getAge();
        if ($age >= 18 && $age <= 29) {
            $price += 50;
        } elseif ($age >= 30 && $age <= 59) {
            $price += 20;
        } elseif ($age >= 60) {
            $price += 100;
        }

        // Tipo de vehículo
        switch ($car->getType()) {
            case Car::TYPE_TURISMO:
                $price += 30;
                break;
            case Car::TYPE_SUV:
                $price += 200;
                break;
            case Car::TYPE_COMPACTO:
                $price += 0;
                break;
        }

        $price = round($price, 2);
        
        return new Money($price, 'EUR');
    }
}
