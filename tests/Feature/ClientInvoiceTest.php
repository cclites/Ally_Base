<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientInvoiceTest extends TestCase
{
    /**
     * @test
     */
    public function allowance_payer_before_a_split_payer_does_not_skew_the_amounts()
    {
        /*
         * Payer A has an allowance of $50.00.  Shift A is $100.00 of services.
         * Payer B is a split payer of 50%, Payer C is the same.
         *
         * Payer A needs to pay $50.00, Payer B & C both need to pay $25.00.
         */

        $this->assertFalse(true);
    }

    /**
     * @test
     */
    public function allowance_payer_accounts_for_credit_adjustments_before_throwing_exception()
    {

        /*
         * Allowance payer has a $100.00 allowance.
         * The shifts assigned to this payer total $150.00, but the Payer has $50.00 in credit adjustments.
         * The invoice should be successfully generated for $100.00 due, taking the credit into account before issuing a PayerAllowanceExceeded exception
         */

        $this->assertFalse(true);
    }

    /**
     * @test
     */
    public function services_on_previous_dates_should_not_be_billed_to_the_current_payer()
    {
        /*
         * Payer A has an effective end of December 31st.  Payer B has an effective start on January 1st.
         * A shift occurring on December 30th is invoiced on January 2nd,  the shift should be billed to Payer A, not Payer B.
         * A shift occurring on January 1st should be invoiced to Payer B, not Payer A.
         */

        $this->assertFalse(true);
    }
}
