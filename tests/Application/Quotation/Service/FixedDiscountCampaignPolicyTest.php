<?php

namespace App\Tests\Domain\Quotation\Service;

use App\Domain\Quotation\Model\Quote;
use App\Domain\Quotation\Service\FixedDiscountCampaignPolicy;
use App\Domain\Quotation\Model\Money;
use PHPUnit\Framework\TestCase;

final class FixedDiscountCampaignPolicyTest extends TestCase
{
    public function test_inactive_campaign_does_not_change_quote(): void
    {
        $policy = new FixedDiscountCampaignPolicy(false, 0.05);

        $quote = new Quote('provider-a', new Money(100.0, 'EUR'));

        $result = $policy->apply($quote);

        $this->assertSame(100.0, $result->getBasePrice()->getAmount());
        $this->assertNull($result->getDiscountedPrice());
    }

    public function test_active_campaign_applies_discount(): void
    {
        $policy = new FixedDiscountCampaignPolicy(true, 0.05);

        $quote = new Quote('provider-a', new Money(200.0, 'EUR'));

        $result = $policy->apply($quote);

        $this->assertNotNull($result->getDiscountedPrice());
        // 200 * 0.95 = 190
        $this->assertSame(190.0, $result->getDiscountedPrice()->getAmount());
        $this->assertSame(200.0, $result->getBasePrice()->getAmount());
    }
}
