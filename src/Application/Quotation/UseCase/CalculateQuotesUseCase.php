<?php

namespace App\Application\Quotation\UseCase;

use App\Application\Quotation\DTO\CalculatedQuotesResponse;
use App\Application\Quotation\DTO\QuoteDTO;
use App\Domain\Quotation\Exception\ProviderException;
use App\Domain\Quotation\Model\Quote;
use App\Domain\Quotation\Model\QuoteRequest;
use App\Domain\Quotation\Port\QuoteProviderPort;
use App\Domain\Quotation\Service\CampaignPolicy;

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

        foreach ($this->providers as $provider) {
            try {
                $quote = $provider->getQuote($request);
                $quotes[] = $quote;
            } catch (ProviderException $e) {
                // Ignorar proveedor que falla, el logging se hará en capa superior.
                continue;
            }
        }

        // Aplicar campaña si está activa
        if ($this->campaignPolicy->isActive()) {
            $quotes = array_map(function (Quote $quote): Quote {
                return $this->campaignPolicy->apply($quote);
            }, $quotes);
        }

        // Ordenar por precio efectivo ascendente
        usort($quotes, function (Quote $a, Quote $b): int {
            $priceA = $a->getEffectivePrice()->getAmount();
            $priceB = $b->getEffectivePrice()->getAmount();

            return $priceA <=> $priceB;
        });

        // Marcar la más barata con nota, si existe
        if (!empty($quotes)) {
            $cheapest = array_shift($quotes);
            $cheapest = $cheapest->withNote('cheapest');
            array_unshift($quotes, $cheapest);
        }

        // Mapear a DTOs
        $offersDto = array_map(function (Quote $quote): QuoteDTO{
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
