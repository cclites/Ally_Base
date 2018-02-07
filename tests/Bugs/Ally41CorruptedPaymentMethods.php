<?php

namespace Tests\Bugs;

use App\BankAccount;
use App\Client;
use App\CreditCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Ally41CorruptedPaymentMethods extends TestCase
{
    use RefreshDatabase;

    public function test_a_credit_card_can_be_reentered_without_corruption()
    {
        /**
         * @var CreditCard $card
         */
        $card1 = factory(CreditCard::class)->create(['user_id' => 1]);
        $number = $card1->number;

        $card2 = factory(CreditCard::class)->make();
        $card2->number = $number;
        $card1->mergeWith($card2);

        $this->assertEquals($number, $card1->number);
    }

    public function test_a_bank_account_can_be_reentered_without_corruption()
    {
        /**
         * @var \App\BankAccount $card
         */
        $account1 = factory(BankAccount::class)->create(['user_id' => 1]);
        $number = $account1->account_number;
        $routing = $account1->routing_number;

        $account2 = factory(BankAccount::class)->make();
        $account2->account_number = $number;
        $account2->routing_number = $routing;
        $account1->mergeWith($account2);

        $this->assertEquals($number, $account1->account_number);
    }
}