<?php

namespace App\Tests\Domain\Quotation\Service;

use App\Domain\Quotation\Model\Car;
use App\Domain\Quotation\Model\Driver;
use App\Domain\Quotation\Model\QuoteRequest;
use App\Domain\Quotation\Service\ProviderBPricingPolicy;
use PHPUnit\Framework\TestCase;

final class ProviderBPricingPolicyTest extends TestCase
{
    /**
     * Base 250 + 50 (18-29) + 30 (turismo) = 330
     */
    public function test_young_turismo(): void
    {
        $policy = new ProviderBPricingPolicy();
        $driver = new Driver(25);
        $car = new Car(Car::TYPE_TURISMO, Car::USE_PRIVADO);
        $request = new QuoteRequest($driver, $car);

        $money = $policy->calculatePrice($request);

        $this->assertSame(330.0, $money->getAmount());
    }

    /**
     * Base 250 + 20 (30-59) + 200 (SUV) = 470
     */
    public function test_middle_age_suv(): void
    {
        $policy = new ProviderBPricingPolicy();
        $driver = new Driver(35);
        $car = new Car(Car::TYPE_SUV, Car::USE_PRIVADO);
        $request = new QuoteRequest($driver, $car);

        $money = $policy->calculatePrice($request);

        $this->assertSame(470.0, $money->getAmount());
    }

    /**
     * Base 250 + 100 (60+) + 0 (compacto) = 350
     */
    public function test_senior_compacto(): void
    {
        $policy = new ProviderBPricingPolicy();
        $driver = new Driver(70);
        $car = new Car(Car::TYPE_COMPACTO, Car::USE_PRIVADO);
        $request = new QuoteRequest($driver, $car);

        $money = $policy->calculatePrice($request);

        $this->assertSame(350.0, $money->getAmount());
    }
}
