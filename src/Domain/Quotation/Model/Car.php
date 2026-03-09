<?php

namespace App\Domain\Quotation\Model;

final class Car
{
    public const TYPE_TURISMO = 'turismo';
    public const TYPE_SUV = 'suv';
    public const TYPE_COMPACTO = 'compacto';

    public const USE_PRIVADO = 'privado';
    public const USE_COMERCIAL = 'comercial';

    private string $type;
    private string $use;

    public function __construct(string $type, string $use)
    {
        $type = mb_strtolower($type);
        $use = mb_strtolower($use);

        if (!in_array($type, [self::TYPE_TURISMO, self::TYPE_SUV, self::TYPE_COMPACTO])) {
            throw new \InvalidArgumentException(sprintf('Invalid car type "%s".', $type));
        }

        if (!in_array($use, [self::USE_PRIVADO, self::USE_COMERCIAL], true)) {
            throw new \InvalidArgumentException(sprintf('Invalid car use "%s".', $use));
        }

        $this->type = $type;
        $this->use = $use;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getUse(): string
    {
        return $this->use;
    }

    public function isCommercialUse(): bool
    {
        return $this->use === self::USE_COMERCIAL;
    }
}
