<?php

namespace App\Infrastructure\Quotation\Provider;

use App\Domain\Quotation\Exception\ProviderTimeoutException;
use App\Domain\Quotation\Exception\ProviderUnavailableException;
use App\Domain\Quotation\Model\Quote;
use App\Domain\Quotation\Model\QuoteRequest;
use App\Domain\Quotation\Port\QuoteProviderPort;
use App\Domain\Quotation\Model\Money;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ProviderBHttpAdapter implements QuoteProviderPort
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
        return 'provider-b';
    }

    public function getQuote(QuoteRequest $request): Quote
    {
        $driver = $request->getDriver();
        $car = $request->getCar();

        // Construir XML de solicitud
        $xml = sprintf(
            '<SolicitudCotizacion>
                <EdadConductor>%d</EdadConductor>
                <TipoCoche>%s</TipoCoche>
                <UsoCoche>%s</UsoCoche>
                <ConductorOcasional>NO</ConductorOcasional>
            </SolicitudCotizacion>',
            $driver->getAge(),
            $car->getType(),
            $car->getUse()
        );

        try {
            $response = $this->httpClient->request('POST', $this->baseUrl . '/provider-b/quote', [
                'body'    => $xml,
                'headers' => ['Content-Type' => 'application/xml'],
                'timeout' => $this->timeout,
            ]);
        } catch (TransportExceptionInterface $e) {
            throw new ProviderTimeoutException('Timeout calling provider B', 0, $e);
        }

        $statusCode = $response->getStatusCode();

        if ($statusCode >= 500) {
            throw new ProviderUnavailableException('Provider B is unavailable (status code: ' . $statusCode . ')');
        }

        if ($statusCode >= 400) {
            throw new ProviderUnavailableException('Provider B returned error (status code: ' . $statusCode . ')');
        }

        $content = $response->getContent();

        $xmlResponse = @simplexml_load_string($content);
        if ($xmlResponse === false) {
            throw new ProviderUnavailableException('Invalid XML from provider B');
        }

        $price = (float) ($xmlResponse->Precio ?? 0);
        $currency = (string) ($xmlResponse->Moneda ?? 'EUR');

        $money = new Money($price, $currency);

        return new Quote($this->getName(), $money);
    }
}
