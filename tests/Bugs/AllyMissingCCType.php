<?php

namespace Tests\Bugs;

use App\Billing\PaymentMethods\CreditCard;
use Tests\TestCase;

class AllyMissingCCType extends TestCase
{

    public function test_a_credit_card_type_is_set_when_a_cc_number_is_entered()
    {
        /**
         * @var CreditCard $card
         */
        $card = factory(CreditCard::class)->make();
        $card->type = null;

        $card->number = '4111111111111111';
        $this->assertEquals('visa', $card->type);

        $card->number = '378734493671000';
        $this->assertEquals('amex', $card->type);
    }

}