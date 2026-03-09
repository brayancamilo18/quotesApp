<?php

namespace App\Infrastructure\Quotation\Provider;

use App\Domain\Quotation\Exception\ProviderException;
use App\Domain\Quotation\Model\Quote;
use App\Domain\Quotation\Model\QuoteRequest;
use App\Domain\Quotation\Port\QuoteProviderPort;
use App\Domain\Quotation\Service\ProviderAPricingPolicy;

final class ProviderAInMemoryAdapter implements QuoteProviderPort
{
    private ProviderAPricingPolicy $pricingPolicy;

    public function __construct(ProviderAPricingPolicy $pricingPolicy)
    {
        $this->pricingPolicy = $pricingPolicy;
    }

    public function getName(): string
    {
        return 'provider-a';
    }

    /**
     * @throws ProviderException
     */
    public function getQuote(QuoteRequest $request): Quote
    {
        // Aquí no simulamos errores todavía; se usarán en el adaptador HTTP.
        $price = $this->pricingPolicy->calculatePrice($request);

        return new Quote($this->getName(), $price);
    }
}
