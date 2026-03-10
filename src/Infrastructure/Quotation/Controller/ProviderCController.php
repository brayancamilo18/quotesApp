<?php

namespace App\Infrastructure\Quotation\Controller;

use App\Domain\Quotation\Model\Car;
use App\Domain\Quotation\Model\Driver;
use App\Domain\Quotation\Model\QuoteRequest;
use App\Domain\Quotation\Service\ProviderCPricingPolicy;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProviderCController
{
    public function __construct(
        private ProviderCPricingPolicy $pricingPolicy,
    ) {}

    #[Route(path: '/provider-c/quote', name: 'provider_c_quote', methods: ['POST'])]
    public function quote(Request $request): Response
    {
        // Simular latencia distinta si quieres
        sleep(3); // 3s
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
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

        $csv = "price,currency\n";
        $csv .= sprintf("%.2f,%s\n", $price->getAmount(), $price->getCurrency());

        return new Response(
            $csv,
            Response::HTTP_OK,
            ['Content-Type' => 'text/csv']
        );
    }
}
