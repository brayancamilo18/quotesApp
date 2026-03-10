<?php

namespace App\Domain\Quotation\Port;

use App\Domain\Quotation\Model\QuoteRequest;
use App\Domain\Quotation\Model\Quote;
use Symfony\Contracts\HttpClient\ResponseInterface;

interface QuoteProviderPort
{
    public function getName(): string;

    /**
     * Lanza la petición HTTP sin bloquear y devuelve la ResponseInterface.
     */
    public function requestAsync(QuoteRequest $request): ResponseInterface;

    /**
     * A partir de una ResponseInterface completada, construye la Quote
     * o lanza una ProviderException si hay problema.
     */
    public function buildQuoteFromResponse(ResponseInterface $response, QuoteRequest $request): Quote;
}