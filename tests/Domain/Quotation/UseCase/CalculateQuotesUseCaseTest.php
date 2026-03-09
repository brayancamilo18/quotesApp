<?php


namespace App\Tests\Application\Quotation\UseCase;

use App\Application\Quotation\UseCase\CalculateQuotesUseCase;
use App\Domain\Quotation\Exception\ProviderException;
use App\Domain\Quotation\Model\Car;
use App\Domain\Quotation\Model\Driver;
use App\Domain\Quotation\Model\Money;
use App\Domain\Quotation\Model\Quote;
use App\Domain\Quotation\Model\QuoteRequest;
use App\Domain\Quotation\Port\QuoteProviderPort;
use App\Domain\Quotation\Service\CampaignPolicy;
use PHPUnit\Framework\TestCase;

final class CalculateQuotesUseCaseTest extends TestCase
{
    public function test_it_orders_quotes_and_marks_cheapest(): void
    {
        $request = new QuoteRequest(
            new Driver(30),
            new Car(Car::TYPE_TURISMO, Car::USE_PRIVADO)
        );

        $providerHigh = new class implements QuoteProviderPort {
            public function getName(): string
            {
                return 'provider-high';
            }
            public function getQuote(QuoteRequest $request): Quote
            {
                return new Quote($this->getName(), new Money(300.0, 'EUR'));
            }
        };

        $providerLow = new class implements QuoteProviderPort {
            public function getName(): string
            {
                return 'provider-low';
            }
            public function getQuote(QuoteRequest $request): Quote
            {
                return new Quote($this->getName(), new Money(200.0, 'EUR'));
            }
        };

        $campaign = new class implements CampaignPolicy {
            public function isActive(): bool
            {
                return false;
            }
            public function getDiscountPercentage(): float
            {
                return 0.05;
            }
            public function apply(Quote $quote): Quote
            {
                return $quote;
            }
        };

        $useCase = new CalculateQuotesUseCase([$providerHigh, $providerLow], $campaign);

        $response = $useCase->execute($request);

        $this->assertFalse($response->isCampaignActive());
        $offers = $response->getOffers();
        $this->assertCount(2, $offers);
        $this->assertSame('provider-low', $offers[0]->getProvider());
        $this->assertSame('cheapest', $offers[0]->getNote());
        $this->assertSame('provider-high', $offers[1]->getProvider());
    }

    public function test_it_ignores_providers_that_fail(): void
    {
        $request = new QuoteRequest(
            new Driver(30),
            new Car(Car::TYPE_TURISMO, Car::USE_PRIVADO)
        );

        $failingProvider = new class implements QuoteProviderPort {
            public function getName(): string
            {
                return 'provider-fail';
            }
            public function getQuote(QuoteRequest $request): Quote
            {
                throw new ProviderException('fail');
            }
        };

        $okProvider = new class implements QuoteProviderPort {
            public function getName(): string
            {
                return 'provider-ok';
            }
            public function getQuote(QuoteRequest $request): Quote
            {
                return new Quote($this->getName(), new Money(250.0, 'EUR'));
            }
        };

        $campaign = new class implements CampaignPolicy {
            public function isActive(): bool
            {
                return false;
            }
            public function getDiscountPercentage(): float
            {
                return 0.05;
            }
            public function apply(Quote $quote): Quote
            {
                return $quote;
            }
        };

        $useCase = new CalculateQuotesUseCase([$failingProvider, $okProvider], $campaign);

        $response = $useCase->execute($request);

        $offers = $response->getOffers();
        $this->assertCount(1, $offers);
        $this->assertSame('provider-ok', $offers[0]->getProvider());
    }

    public function test_it_applies_campaign_discount(): void
    {
        $request = new QuoteRequest(
            new Driver(30),
            new Car(Car::TYPE_TURISMO, Car::USE_PRIVADO)
        );

        $provider = new class implements QuoteProviderPort {
            public function getName(): string
            {
                return 'provider-a';
            }
            public function getQuote(QuoteRequest $request): Quote
            {
                return new Quote($this->getName(), new Money(200.0, 'EUR'));
            }
        };

        $campaign = new class implements CampaignPolicy {
            public function isActive(): bool
            {
                return true;
            }
            public function getDiscountPercentage(): float
            {
                return 0.05;
            }
            public function apply(Quote $quote): Quote
            {
                $base = $quote->getBasePrice();
                $discounted = new Money($base->getAmount() * 0.95, $base->getCurrency());

                return $quote->withDiscountedPrice($discounted);
            }
        };

        $useCase = new CalculateQuotesUseCase([$provider], $campaign);

        $response = $useCase->execute($request);

        $this->assertTrue($response->isCampaignActive());
        $offers = $response->getOffers();
        $this->assertCount(1, $offers);
        $this->assertSame(200.0, $offers[0]->getPrice());
        $this->assertSame(190.0, $offers[0]->getDiscountedPrice());
    }
}
