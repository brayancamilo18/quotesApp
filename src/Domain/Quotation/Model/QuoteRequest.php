<?php

namespace App\Domain\Quotation\Model;

final class QuoteRequest
{
    private Driver $driver;
    private Car $car;

    public function __construct(Driver $driver, Car $car)
    {
        $this->driver = $driver;
        $this->car = $car;
    }

    public function getDriver(): Driver
    {
        return $this->driver;
    }

    public function getCar(): Car
    {
        return $this->car;
    }
}