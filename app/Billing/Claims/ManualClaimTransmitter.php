<?php

namespace App\Billing\Claims;

use App\Billing\Claim;
use App\Billing\ClientInvoice;
use App\Billing\Contracts\ClaimTransmitterInterface;
use App\Billing\Invoiceable\ShiftService;
use App\Shift;

class ManualClaimTransmitter extends BaseClaimTransmitter implements ClaimTransmitterInterface
{
    /**
     * Validate an invoice has all the required parameters to
     * be transmitted as a claim.
     *
     * @param \App\Billing\ClientInvoice $invoice
     * @return null|array
     */
    public function validateInvoice(ClientInvoice $invoice): ?array
    {
        return null;
    }

    /**
     * Submit the claim using the service.
     *
     * @param Claim $claim
     * @return bool
     */
    public function send(Claim $claim): bool
    {
        return true;
    }

    /**
     * Map a claim's shift into importable data for the service.
     *
     * @param \App\Billing\Claim $claim
     * @param \App\Shift $shift
     * @return array
     */
    public function mapShiftRecord(Claim $claim, Shift $shift): array
    {
        return [];
    }

    /**
     * Map a claim's shift into importable data for the service.
     *
     * @param \App\Billing\Claim $claim
     * @param ShiftService $shiftService
     * @return array
     */
    public function mapServiceRecord(Claim $claim, ShiftService $shiftService) : array
    {
        return [];
    }
}