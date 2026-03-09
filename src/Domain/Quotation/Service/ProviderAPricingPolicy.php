<?php

namespace App\Domain\Quotation\Service;

use App\Domain\Quotation\Model\Car;
use App\Domain\Quotation\Model\QuoteRequest;
use App\Domain\Quotation\Model\Money;

final class ProviderAPricingPolicy
{
    private const BASE_PRICE = 217.0;

    public function calculatePrice(QuoteRequest $request): Money
    {
        $driver = $request->getDriver();
        $car = $request->getCar();

        $price = self::BASE_PRICE;

        // Edad
        $age = $driver->getAge();
        if ($age >= 18 && $age <= 24) {
            $price += 70;
        } else if ($age >= 56) {
            $price += 90;
        }

        // Tipo de vehículo
        switch ($car->getType()) {
            case Car::TYPE_SUV;
                $price += 100;
                break;
            case Car::TYPE_TURISMO:
            case Car::TYPE_COMPACTO:
                $price += 10;
                break;
        }

        // Uso comercial
        if($car->isCommercialUse()){
            $price = $price * 1.15;
        }

        $price = round($price, 2);


        return new Money($price, 'EUR');
    }
}
