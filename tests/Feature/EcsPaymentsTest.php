<?php

namespace Tests\Feature;

use App\Billing\Payments\Methods\BankAccount;
use App\Billing\Payments\Methods\CreditCard;
use App\Billing\Exceptions\PaymentMethodDeclined;
use App\Billing\Exceptions\PaymentMethodError;
use App\Billing\Gateway\ECSPayment;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EcsPaymentsTest extends TestCase
{
    use RefreshDatabase;

    protected $testCard = '4111111111111111';
    protected $testAccount = '123123123';
    protected $testRouting = '123123123';

    protected $card;
    protected $account;

    public function setUp() : void
    {
        parent::setUp();
        $this->card = new CreditCard([
            'number' => $this->testCard,
            'expiration_month' => 10,
            'expiration_year' => 2025,
            'name_on_card' => 'John Smith',
        ]);
        $this->account = new BankAccount([
            'account_number' => $this->testAccount,
            'routing_number' => $this->testRouting,
            'account_type' => 'checking',
            'account_holder_type' => 'personal',
            'name_on_account' => 'John Smith'
        ]);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreditCardValidation()
    {
        $ecs = new ECSPayment();
        $transaction = $ecs->validateCard($this->card, 999);
        $this->assertTrue($transaction->success);
        $this->assertTrue($transaction->cvv_pass);
    }

    public function testCreditCardPayment()
    {
        $ecs = new ECSPayment();
        $transaction = $ecs->chargeCard($this->card, 100.50);
        $this->assertTrue($transaction->success);
        $this->assertEquals(100.50, $transaction->amount);
    }

    public function testAchPayment()
    {
        $ecs = new ECSPayment();
        $transaction = $ecs->chargeAccount($this->account, 25.00);
        $this->assertTrue($transaction->success);
        $this->assertEquals(25.00, $transaction->amount);
    }

    public function testAchDeposit()
    {
        $ecs = new ECSPayment();
        $transaction = $ecs->depositFunds($this->account, 25.00);
        $this->assertTrue($transaction->success);
        $this->assertEquals(25.00, $transaction->amount);
    }

    public function testCCDeclinedStillCreatesTransaction()
    {
        $ecs = new ECSPayment();
        $transaction = $ecs->chargeCard($this->card, 0.50);
        $this->assertEquals(true, $transaction->declined);
        $this->assertEquals(false, $transaction->success);
    }

    public function testInvalidCCThrowsException()
    {
        $ecs = new ECSPayment();
        $card = clone $this->card;
        $card->number = '41111111234';

        $this->expectException(PaymentMethodError::class);
        $transaction = $ecs->validateCard($card);
    }

    public function testInvalidBankAccountThrowsException()
    {
        $ecs = new ECSPayment();
        $account = clone $this->account;
        $account->account_number = '41111111234';

        $this->expectException(PaymentMethodError::class);
        $transaction = $ecs->validateAccount($account);
    }
}
