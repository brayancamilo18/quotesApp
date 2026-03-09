<?php

namespace App\Infrastructure\Quotation\Provider;

use App\Domain\Quotation\Exception\ProviderTimeoutException;
use App\Domain\Quotation\Exception\ProviderUnavailableException;
use App\Domain\Quotation\Model\Money;
use App\Domain\Quotation\Model\Quote;
use App\Domain\Quotation\Model\QuoteRequest;
use App\Domain\Quotation\Port\QuoteProviderPort;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ProviderAHttpAdapter implements QuoteProviderPort
{
    private HttpClientInterface $httpClient;
    private string $baseUrl;
    private float $timeout;

    public function __construct(HttpClientInterface $httpClient, string $baseUrl, float $timeout = 10.0)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
    }

    public function getName(): string
    {
        return 'provider-a';
    }

    public function getQuote(QuoteRequest $request): Quote
    {
        $driver = $request->getDriver();
        $car = $request->getCar();

        $payload = [
            'driver_age' => $driver->getAge(),
            'car_type'   => $car->getType(),
            'car_use'    => $car->getUse(),
        ];

        try {
            $response = $this->httpClient->request('POST', $this->baseUrl . '/provider-a/quote', [
                'json'    => $payload,
                'timeout' => $this->timeout,
            ]);
        } catch (TransportExceptionInterface $e) {
            throw new ProviderTimeoutException('Timeout calling provider A', 0, $e);
        }

        $statusCode = $response->getStatusCode();

        if ($statusCode >= 500) {
            throw new ProviderUnavailableException('Provider A is unavailable (status code: ' . $statusCode . ')');
        }

        if ($statusCode >= 400) {
            throw new ProviderUnavailableException('Provider A returned error (status code: ' . $statusCode . ')');
        }

        $data = $response->toArray(false);

        $price = (float) ($data['price'] ?? 0);
        $currency = (string) ($data['currency'] ?? 'EUR');

        $money = new Money($price, $currency);

        return new Quote($this->getName(), $money);
    }
}
