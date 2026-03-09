<?php

namespace App\Domain\Quotation\Port;

use App\Domain\Quotation\Exception\ProviderException;
use App\Domain\Quotation\Model\Quote;
use App\Domain\Quotation\Model\QuoteRequest;

interface QuoteProviderPort
{
    public function getName(): string;

    /**
     * @throws ProviderException
     */
    public function getQuote(QuoteRequest $request): Quote;
}