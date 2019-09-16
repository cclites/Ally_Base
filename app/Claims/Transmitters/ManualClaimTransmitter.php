<?php

namespace App\Claims\Transmitters;

use App\Claims\Contracts\ClaimTransmitterInterface;
use App\Claims\ClaimInvoiceItem;
use App\Claims\ClaimInvoice;

class ManualClaimTransmitter extends BaseClaimTransmitter implements ClaimTransmitterInterface
{
    /**
     * Validate a ClaimInvoice has all the required parameters to
     * be transmitted to the service.
     *
     * @param ClaimInvoice $claim
     * @return null|array
     */
    public function validateClaim(ClaimInvoice $claim): ?array
    {
        return null;
    }

    /**
     * Submit the claim using the service.
     *
     * @param ClaimInvoice $claim
     * @return bool
     */
    public function send(ClaimInvoice $claim): bool
    {
        return true;
    }

    /**
     * Map a ClaimInvoiceItem record to the importable
     * data for the service.
     *
     * @param ClaimInvoiceItem $item
     * @return array
     */
    public function mapClaimableRecord(ClaimInvoiceItem $item): array
    {
        return [];
    }
}