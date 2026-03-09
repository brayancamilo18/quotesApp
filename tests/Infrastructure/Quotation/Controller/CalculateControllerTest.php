<?php

namespace App\Tests\Infrastructure\Quotation\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CalculateControllerTest extends WebTestCase
{
    public function test_calculate_returns_offers(): void
    {
        $client = static::createClient();

        $payload = [
            'driver_birthday' => '1990-01-01',
            'car_type'        => 'turismo',
            'car_use'         => 'privado',
        ];

        $client->request(
            'POST',
            '/calculate',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('campaign_active', $data);
        $this->assertArrayHasKey('offers', $data);

        $this->assertIsArray($data['offers']);
    }
}
