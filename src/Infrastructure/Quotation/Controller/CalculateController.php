<?php

namespace App\Infrastructure\Quotation\Controller;

use App\Application\Quotation\UseCase\CalculateQuotesUseCase;
use App\Domain\Quotation\Model\Car;
use App\Domain\Quotation\Model\Driver;
use App\Domain\Quotation\Model\QuoteRequest;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class CalculateController
{
    private CalculateQuotesUseCase $useCase;

    public function __construct(CalculateQuotesUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[Route('/calculate', name: 'calculate_quotes', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $driverBirthday = $data['driver_birthday'] ?? null;
        $carType        = $data['car_type'] ?? null;
        $carUse         = $data['car_use'] ?? null;

        if (!$driverBirthday || !$carType || !$carUse) {
            return new JsonResponse(['error' => 'Missing required fields'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $age = $this->calculateAgeFromBirthday($driverBirthday);
            $driver = new Driver($age);
            $car = new Car($carType, $carUse);
            $quoteRequest = new QuoteRequest($driver, $car);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }

        $response = $this->useCase->execute($quoteRequest);

        $offers = [];

        foreach($response->getOffers() as $offerDto){
            $offers[] = [
                'provider'         => $offerDto->getProvider(),
                'price'            => $offerDto->getPrice(),
                'discounted_price' => $offerDto->getDiscountedPrice(),
                'currency'         => $offerDto->getCurrency(),
                'note'             => $offerDto->getNote(),
            ];
        }

        return new JsonResponse([
            'campaign_active' => $response->isCampaignActive(),
            'offers'          => $offers,
        ]);
    }

    private function calculateAgeFromBirthday(string $birthday): int
    {
        $birthDate = new DateTimeImmutable($birthday);
        $now = new DateTimeImmutable('now');

        $diff = $now->diff($birthDate);
        return $diff->y;
    }
}
