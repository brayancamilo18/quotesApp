<?php

namespace App\Infrastructure\Quotation\Provider;

use App\Domain\Quotation\Exception\ProviderException;
use App\Domain\Quotation\Model\Quote;
use App\Domain\Quotation\Model\QuoteRequest;
use App\Domain\Quotation\Port\QuoteProviderPort;
use App\Domain\Quotation\Service\ProviderBPricingPolicy;

final class ProviderBInMemoryAdapter implements QuoteProviderPort
{
    private ProviderBPricingPolicy $pricingPolicy;

    public function __construct(ProviderBPricingPolicy $pricingPolicy)
    {
        $this->pricingPolicy = $pricingPolicy;
    }

    public function getName(): string
    {
        return 'provider-b';
    }

    /**
     * @throws ProviderException
     */
    public function getQuote(QuoteRequest $request): Quote
    {
        $price = $this->pricingPolicy->calculatePrice($request);

        return new Quote($this->getName(), $price);
    }
}
