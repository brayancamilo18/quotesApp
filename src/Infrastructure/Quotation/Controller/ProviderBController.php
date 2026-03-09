<?php

namespace App\Infrastructure\Quotation\Controller;

use App\Domain\Quotation\Model\Car;
use App\Domain\Quotation\Model\Driver;
use App\Domain\Quotation\Model\QuoteRequest;
use App\Domain\Quotation\Service\ProviderBPricingPolicy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProviderBController
{
    private ProviderBPricingPolicy $pricingPolicy;

    public function __construct(ProviderBPricingPolicy $pricingPolicy)
    {
        $this->pricingPolicy = $pricingPolicy;
    }

    #[Route('/provider-b/quote', name: 'provider_b_quote', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        // Simular latencia normal
        sleep(5);

        // 1% de las llamadas: tardar ~60s
        if (mt_rand(1, 100) === 1) {
            sleep(60);
        }

        $content = $request->getContent();
        if (empty($content)) {
            return new Response('Invalid XML', Response::HTTP_BAD_REQUEST);
        }

        $xml = @simplexml_load_string($content);
        if ($xml === false) {
            return new Response('Invalid XML', Response::HTTP_BAD_REQUEST);
        }

        $edadConductor = (int) ($xml->EdadConductor ?? 0);
        $tipoCoche     = (string) ($xml->TipoCoche ?? '');
        $usoCoche      = (string) ($xml->UsoCoche ?? '');

        try {
            $driver = new Driver($edadConductor);
            $car = new Car($tipoCoche, $usoCoche);
            $quoteRequest = new QuoteRequest($driver, $car);

            $price = $this->pricingPolicy->calculatePrice($quoteRequest);
        } catch (\Throwable $e) {
            return new Response('Invalid data: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $responseXml = sprintf(
            '<RespuestaCotizacion>
                <Precio>%s</Precio>
                <Moneda>%s</Moneda>
             </RespuestaCotizacion>',
            $price->getAmount(),
            $price->getCurrency()
        );

        return new Response($responseXml, Response::HTTP_OK, ['Content-Type' => 'application/xml']);
    }
}
