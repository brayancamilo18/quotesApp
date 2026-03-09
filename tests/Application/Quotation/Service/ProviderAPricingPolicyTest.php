<?php

namespace App\Tests\Domain\Quotation\Service;

use App\Domain\Quotation\Model\Car;
use App\Domain\Quotation\Model\Driver;
use App\Domain\Quotation\Model\QuoteRequest;
use App\Domain\Quotation\Service\ProviderAPricingPolicy;
use PHPUnit\Framework\TestCase;

final class ProviderAPricingPolicyTest extends TestCase
{
    /**
     * Base 217 + 70 (18-24) + 100 (SUV) = 387 * 1.15 (uso comercial) = 445.05
     */

    public function test_young_driver_suv_commercial(): void
    {
        $policy = new ProviderAPricingPolicy();
        $driver = new Driver(22);
        $car = new Car(Car::TYPE_SUV, Car::USE_COMERCIAL);
        $request = new QuoteRequest($driver, $car);
        $money = $policy->calculatePrice($request);

        $this->assertEquals(445.05, $money->getAmount());
        $this->assertSame('EUR', $money->getCurrency());
    }

    /**
     * Base 217 + 0 (25-55) + 10 (turismo) = 227
     */

    public function test_middle_age_turismo_private(): void
    {
        $policy = new ProviderAPricingPolicy();
        $driver = new Driver(40);
        $car = new Car(Car::TYPE_TURISMO, Car::USE_PRIVADO);
        $request = new QuoteRequest($driver, $car);

        $money = $policy->calculatePrice($request);

        $this->assertSame(227.0, $money->getAmount());
    }

    /**
     * Base 217 + 90 (56+) + 10 (compacto) = 317
     */
    public function test_senior_compacto_private(): void
    {
        $policy = new ProviderAPricingPolicy();
        $driver = new Driver(60);
        $car = new Car(Car::TYPE_COMPACTO, Car::USE_PRIVADO);
        $request = new QuoteRequest($driver, $car);

        $money = $policy->calculatePrice($request);

        $this->assertSame(317.0, $money->getAmount());
    }
}
