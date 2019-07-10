<?php
namespace App\Billing\Contracts;

use App\Billing\Claim;
use App\Billing\ClientInvoice;
use App\Billing\Invoiceable\ShiftService;
use App\Shift;

interface ClaimTransmitterInterface
{
    /**
     * Validate an invoice has all the required parameters to
     * be transmitted as a claim.
     *
     * @param \App\Billing\ClientInvoice $invoice
     * @return null|array
     * @throws \App\Billing\Exceptions\ClaimTransmissionException
     */
    public function validateInvoice(ClientInvoice $invoice) : ?array;

    /**
     * Submit the claim using the service.
     *
     * @param \App\Billing\Claim $claim
     * @return bool
     * @throws \App\Billing\Exceptions\ClaimTransmissionException
     */
    public function send(Claim $claim) : bool;

    /**
     * Map a claim's shift into importable data for the service.
     *
     * @param \App\Billing\Claim $claim
     * @param \App\Shift $shift
     * @return array
     */
    public function mapShiftRecord(Claim $claim, Shift $shift) : array;

    /**
     * Map a claim's shift into importable data for the service.
     *
     * @param \App\Billing\Claim $claim
     * @param ShiftService $shiftService
     * @return array
     */
    public function mapServiceRecord(Claim $claim, ShiftService $shiftService) : array;

    /**
     * Check transmitter is in test mode.
     *
     * @param Claim $claim
     * @return bool
     */
    public function isTestMode(Claim $claim) : bool;

    /**
     * Create and return the Claim path of the file that would be transmitted.
     *
     * @param Claim $claim
     * @return null|string
     * @throws \Exception
     */
    public function test(Claim $claim) : ?string;
}