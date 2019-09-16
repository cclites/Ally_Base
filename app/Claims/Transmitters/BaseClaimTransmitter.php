<?php

namespace App\Claims\Transmitters;

use App\Claims\ClaimInvoiceItem;
use App\Claims\Exceptions\ClaimTransmissionException;
use App\Claims\Contracts\ClaimTransmitterInterface;
use App\Claims\ClaimInvoice;

abstract class BaseClaimTransmitter implements ClaimTransmitterInterface
{
    /**
     * Validate a ClaimInvoice has all the required parameters to
     * be transmitted to the service.
     *
     * @param ClaimInvoice $claim
     * @return null|array
     * @throws ClaimTransmissionException
     */
    public function validateClaim(ClaimInvoice $claim): ?array
    {
        $errors = ['business' => [], 'payer' => [], 'client' => [], 'credentials' => []];

        if (empty($claim->business->ein)) {
            array_push($errors['business'], 'ein');
        }

        if (empty($claim->client_medicaid_id)) {
            array_push($errors['client'], 'medicaid_id');
        }

        if ($claim->items()->count() === 0) {
            throw new ClaimTransmissionException('You cannot transmit this Claim because there are no claimable items attached.');
        }

        return $errors;
    }

    /**
     * Check transmitter is in test mode.
     *
     * @param ClaimInvoice $claim
     * @return bool
     */
    public function isTestMode(ClaimInvoice $claim): bool
    {
        return false;
    }

    /**
     * Create and return the Claim file/data that would be transmitted.
     *
     * @param ClaimInvoice $claim
     * @return null|string
     * @throws \Exception
     */
    public function test(ClaimInvoice $claim): ?string
    {
        return null;
    }

    /**
     * Submit the claim using the service.
     *
     * @param ClaimInvoice $claim
     * @return bool
     * @throws \App\Billing\Exceptions\ClaimTransmissionException
     */
    abstract public function send(ClaimInvoice $claim): bool;

    /**
     * Map a claim's shift into importable data for the service.
     *
     * @param ClaimInvoiceItem $item
     * @return array
     */
    abstract public function mapClaimableRecord(ClaimInvoiceItem $item): array;
}