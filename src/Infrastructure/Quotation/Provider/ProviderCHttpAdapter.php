<?php

namespace App\Infrastructure\Quotation\Provider;

use App\Domain\Quotation\Exception\ProviderTimeoutException;
use App\Domain\Quotation\Exception\ProviderUnavailableException;
use App\Domain\Quotation\Model\Money;
use App\Domain\Quotation\Model\Quote;
use App\Domain\Quotation\Model\QuoteRequest;
use App\Domain\Quotation\Port\QuoteProviderPort;
use Symfony\Contracts\HttpClient\Exception\TimeoutExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class ProviderCHttpAdapter implements QuoteProviderPort
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
        return 'provider-c';
    }

    public function requestAsync(QuoteRequest $request): ResponseInterface
    {
        $driver = $request->getDriver();
        $car = $request->getCar();

        $payload = [
            'driver_age' => $driver->getAge(),
            'car_type'   => $car->getType(),
            'car_use'    => $car->getUse(),
        ];

        try {
            return $this->httpClient->request('POST', $this->baseUrl . '/provider-c/quote', [
                'json'    => $payload,
                'timeout' => $this->timeout,
            ]);
        } catch (TransportExceptionInterface $e) {
            throw new ProviderTimeoutException('Timeout calling provider C', 0, $e);
        }
    }

    public function buildQuoteFromResponse(ResponseInterface $response, QuoteRequest $request): Quote
    {
        try {
            $statusCode = $response->getStatusCode();
        } catch (TimeoutExceptionInterface $e) {
            throw new ProviderTimeoutException('Timeout calling provider C', 0, $e);
        } catch (TransportExceptionInterface $e) {
            throw new ProviderTimeoutException('Timeout calling provider C', 0, $e);
        }

        if ($statusCode >= 500) {
            throw new ProviderUnavailableException(
                'Provider C is unavailable (status code: ' . $statusCode . ')'
            );
        }

        if ($statusCode >= 400) {
            throw new ProviderUnavailableException(
                'Provider C returned error (status code: ' . $statusCode . ')'
            );
        }

        try {
            $content = $response->getContent(false);
        } catch (TimeoutExceptionInterface $e) {
            throw new ProviderTimeoutException('Timeout calling provider C', 0, $e);
        } catch (TransportExceptionInterface $e) {
            throw new ProviderTimeoutException('Timeout calling provider C', 0, $e);
        }

        
        $lines = array_values(array_filter(array_map('trim', explode("\n", $content))));
        if (count($lines) < 2) {
            throw new ProviderUnavailableException('Invalid CSV from provider C');
        }

        // segunda línea: datos
        $dataLine = $lines[1];
        $parts = str_getcsv($dataLine);

        if (count($parts) < 2) {
            throw new ProviderUnavailableException('Invalid CSV structure from provider C');
        }

        [$priceString, $currency] = $parts;

        $price = (float) $priceString;
        $currency = (string) $currency ?: 'EUR';

        $money = new Money($price, $currency);

        return new Quote($this->getName(), $money);
    }
}
