<?php

namespace App\Application\Quotation\UseCase;

use App\Application\Quotation\DTO\CalculatedQuotesResponse;
use App\Application\Quotation\DTO\QuoteDTO;
use App\Domain\Quotation\Exception\ProviderException;
use App\Domain\Quotation\Model\Quote;
use App\Domain\Quotation\Model\QuoteRequest;
use App\Domain\Quotation\Port\QuoteProviderPort;
use App\Domain\Quotation\Service\CampaignPolicy;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class CalculateQuotesUseCase
{
    /** @var QuoteProviderPort[] */
    private array $providers;

    private CampaignPolicy $campaignPolicy;

    /**
     * @param QuoteProviderPort[] $providers
     */
    public function __construct(iterable $providers, CampaignPolicy $campaignPolicy)
    {
        $this->providers = is_array($providers) ? $providers : iterator_to_array($providers);
        $this->campaignPolicy = $campaignPolicy;
    }

    public function execute(QuoteRequest $request): CalculatedQuotesResponse
    {
        $quotes = [];

        // 1. Lanzar en paralelo todas las peticiones
        /** @var array<int, array{provider: QuoteProviderPort, response: ResponseInterface}> $asyncRequests */
        $asyncRequests = [];

        foreach ($this->providers as $provider) {
            try {
                $response = $provider->requestAsync($request);
                $asyncRequests[] = [
                    'provider' => $provider,
                    'response' => $response,
                ];
            } catch (ProviderException $e) {
                continue;
            }
        }

        // 2. Recoger todas las respuestas y construir las Quote
        foreach ($asyncRequests as $item) {
            /** @var QuoteProviderPort $provider */
            $provider = $item['provider'];
            /** @var ResponseInterface $response */
            $response = $item['response'];

            try {
                $quote = $provider->buildQuoteFromResponse($response, $request);
                $quotes[] = $quote;
            } catch (ProviderException $e) {
                continue;
            }
        }

        // 3. Aplicar campaña si está activa (igual que antes)
        if ($this->campaignPolicy->isActive()) {
            $quotes = array_map(function (Quote $quote): Quote {
                return $this->campaignPolicy->apply($quote);
            }, $quotes);
        }

        // 4. Ordenar por precio efectivo ascendente (igual que antes)
        usort($quotes, function (Quote $a, Quote $b): int {
            $priceA = $a->getEffectivePrice()->getAmount();
            $priceB = $b->getEffectivePrice()->getAmount();

            return $priceA <=> $priceB;
        });

        // 5. Marcar la más barata con nota "cheapest" (igual que antes)
        if (!empty($quotes)) {
            $cheapest = array_shift($quotes);
            $cheapest = $cheapest->withNote('cheapest');
            array_unshift($quotes, $cheapest);
        }

        // 6. Mapear a DTOs (igual que antes)
        $offersDto = array_map(function (Quote $quote): QuoteDTO {
            $base = $quote->getBasePrice();
            $discounted = $quote->getDiscountedPrice();

            return new QuoteDTO(
                $quote->getProviderName(),
                $base->getAmount(),
                $discounted?->getAmount(),
                $base->getCurrency(),
                $quote->getNote()
            );
        }, $quotes);

        return new CalculatedQuotesResponse(
            $this->campaignPolicy->isActive(),
            $offersDto
        );
    }
}
