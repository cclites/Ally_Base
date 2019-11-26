<?php

namespace Tests\Unit;

use App\Billing\Payments\PaymentMethodType;
use App\Billing\FeeOverrideRule;
use Tests\CreatesBusinesses;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FeeOverrideRuleTest extends TestCase
{
    use RefreshDatabase;
    use CreatesBusinesses;

    public function setUp()
    {
        parent::setUp();
        $this->createBusinessWithUsers(false);
    }

    /** @test */
    function it_can_have_an_override_for_an_entire_business()
    {
        $override = FeeOverrideRule::create([
            'client_id' => null,
            'business_id' => $this->business->id,
            'rate' => 0.03,
            'payment_method_type' => PaymentMethodType::CC(),
        ]);

        $this->assertCount(1, FeeOverrideRule::all());
        $this->assertEquals($override->business_id, $this->business->id);
        $this->assertEquals($override->payment_method_type, PaymentMethodType::CC());
    }

    /** @test */
    function it_can_have_multiple_rates_based_on_payment_method_type()
    {
        $override = FeeOverrideRule::create([
            'client_id' => null,
            'business_id' => $this->business->id,
            'rate' => 0.03,
            'payment_method_type' => PaymentMethodType::CC(),
        ]);

        $override = FeeOverrideRule::create([
            'client_id' => null,
            'business_id' => $this->business->id,
            'rate' => 0.05,
            'payment_method_type' => PaymentMethodType::ACH(),
        ]);

        $this->assertCount(2, FeeOverrideRule::all());

        $rule = FeeOverrideRule::lookup($this->business->id, PaymentMethodType::CC());
        $this->assertEquals(0.03, $rule->rate);

        $rule = FeeOverrideRule::lookup($this->business->id, PaymentMethodType::ACH());
        $this->assertEquals(0.05, $rule->rate);
    }
}