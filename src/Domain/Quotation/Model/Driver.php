<?php

namespace App\Domain\Quotation\Model;

final class Driver
{
    private int $age;

    public function __construct(int $age)
    {
        if ($age < 18) {
            throw new \InvalidArgumentException('Driver must be at least 18 years old.');
        }

        $this->age = $age;
    }

    public function getAge(): int
    {
        return $this->age;
    }
}