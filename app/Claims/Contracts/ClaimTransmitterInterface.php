<?php

namespace App\Claims\Contracts;

use App\Claims\ClaimInvoiceItem;
use App\Claims\ClaimInvoice;

interface ClaimTransmitterInterface
{
    /**
     * Validate a ClaimInvoice has all the required parameters to
     * be transmitted to the service.
     *
     * @param ClaimInvoice $claim
     * @return null|array
     */
    public function validateClaim(ClaimInvoice $claim): ?array;

    /**
     * Submit the claim using the service.
     *
     * @param ClaimInvoice $claim
     * @return bool
     */
    public function send(ClaimInvoice $claim): bool;

    /**
     * Get all claim data to transmit, filtering out
     * and skipped items.
     *
     * @param ClaimInvoice $claim
     * @return array
     */
    public function getData(ClaimInvoice $claim): array;

    /**
     * Map a ClaimInvoiceItem record to the importable
     * data for the service.
     *
     * @param ClaimInvoiceItem $item
     * @return null|array
     */
    public function mapClaimableRecord(ClaimInvoiceItem $item): ?array;

    /**
     * Check transmitter is in test mode.
     *
     * @param ClaimInvoice $claim
     * @return bool
     */
    public function isTestMode(ClaimInvoice $claim): bool;

    /**
     * Create and return the Claim path of the file that would be transmitted.
     *
     * @param ClaimInvoice $claim
     * @return null|string
     * @throws \Exception
     */
    public function test(ClaimInvoice $claim): ?string;
}