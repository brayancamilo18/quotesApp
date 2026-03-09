<?php


namespace App\Infrastructure\Quotation\Controller;

use App\Domain\Quotation\Model\Car;
use App\Domain\Quotation\Model\Driver;
use App\Domain\Quotation\Model\QuoteRequest;
use App\Domain\Quotation\Service\ProviderAPricingPolicy;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class ProviderAController
{
    private ProviderAPricingPolicy $pricingPolicy;

    public function __construct(ProviderAPricingPolicy $pricingPolicy)
    {
        $this->pricingPolicy = $pricingPolicy;
    }

    #[Route('/provider-a/quote', name: 'provider_a_quote', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        // Simulamos latencia
        sleep(2);

        //Simulamos 10% de error
        if (mt_rand(1, 10) === 1) {
            return new JsonResponse(['error' => 'Provider A internal error'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $driverAge = $data['driver_age'] ?? null;
        $carType   = $data['car_type'] ?? null;
        $carUse    = $data['car_use'] ?? null;

        if ($driverAge === null || !$carType || !$carUse) {
            return new JsonResponse(['error' => 'Missing required fields'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $driver = new Driver((int) $driverAge);
            $car = new Car($carType, $carUse);
            $quoteRequest = new QuoteRequest($driver, $car);

            $price = $this->pricingPolicy->calculatePrice($quoteRequest);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'price'    => $price->getAmount(),
            'currency' => $price->getCurrency(),
            'provider' => 'provider-a',
        ]);
    }
}
