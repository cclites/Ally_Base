<?php

namespace Tests\Feature;

use App\Billing\Actions\ProcessPayment;
use App\Billing\Exceptions\PaymentAmountError;
use App\Billing\Exceptions\PaymentMethodDeclined;
use App\Billing\Exceptions\PaymentMethodError;
use App\Billing\GatewayTransaction;
use App\Billing\Payment;
use App\Billing\Payments\Contracts\PaymentMethodStrategy;
use App\Billing\Payments\Methods\CreditCard;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessPaymentTest extends TestCase
{
    use RefreshDatabase;

    private $strategy;

    protected function setUp()
    {
        parent::setUp();
        $this->strategy = \Mockery::mock(PaymentMethodStrategy::class);
        $this->strategy->shouldReceive('getOwnerModel')->andReturn(null);
        $this->strategy->shouldReceive('getPaymentType')->andReturn('CC');
        $this->strategy->shouldReceive('getPaymentMethod')->andReturn(new CreditCard(['id' => 1]));
    }

    /**
     * @test
     */
    public function a_payment_model_is_returned_on_success()
    {
        $this->strategy->shouldReceive('charge')->andReturn(new GatewayTransaction(['success' => true]));

        $processor = new ProcessPayment();
        $payment = $processor->charge($this->strategy, 10);

        $this->assertInstanceOf(Payment::class, $payment);
    }

    /**
     * @test
     */
    public function an_exception_is_thrown_when_the_payment_is_less_than_0()
    {
        $this->expectException(PaymentAmountError::class);
        $processor = new ProcessPayment();
        $payment = $processor->charge($this->strategy, -10);
    }

    /**
     * @test
     */
    public function an_exception_is_thrown_on_a_gateway_failure()
    {
        $this->strategy->shouldReceive('charge')->andReturn(null);
        $this->expectException(PaymentMethodError::class);

        $processor = new ProcessPayment();
        $payment = $processor->charge($this->strategy, 10);
    }

    /**
     * @test
     */
    public function an_exception_is_thrown_on_an_unsuccessful_transaction()
    {
        $this->strategy->shouldReceive('charge')->andReturn(new GatewayTransaction(['success' => false]));
        $this->expectException(PaymentMethodDeclined::class);

        $processor = new ProcessPayment();
        $payment = $processor->charge($this->strategy, 10);
    }

    /**
     * @test
     */
    public function the_ally_fee_calculation_does_not_double_dip()
    {
        $this->strategy->shouldReceive('charge')->andReturn(new GatewayTransaction(['success' => true]));

        $processor = new ProcessPayment();
        $payment = $processor->charge($this->strategy, 21);

        $this->assertEquals(1, $payment->getAllyFee());

        $processor = new ProcessPayment();
        $payment = $processor->charge($this->strategy, 25.25);

        $this->assertEquals(1.20, $payment->getAllyFee());
    }
}
