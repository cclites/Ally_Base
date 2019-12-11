<?php

namespace App\Claims\Transmitters;

use App\Claims\Exceptions\ClaimTransmissionException;
use App\Claims\Contracts\ClaimTransmitterInterface;
use App\Claims\ClaimableService;
use App\Claims\ClaimInvoiceItem;
use App\Claims\ClaimInvoice;

abstract class BaseClaimTransmitter implements ClaimTransmitterInterface
{
    /**
     * Indicates the reason a claim should be prevented
     * from transmission.
     *
     * @param \App\Claims\ClaimInvoice $claim
     * @return null|string
     */
    public function prevent(ClaimInvoice $claim): ?string
    {
        return null;
    }

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
        $errors = collect([]);
        $editClaimUrl = route('business.claims.edit', ['claim' => $claim]);

        if ($claim->items()->count() === 0) {
            throw new ClaimTransmissionException('You cannot transmit this Claim because there are no claimable items attached.');
        }

        if (empty($claim->business->ein)) {
            $errors->push(['message' => 'Your business EIN # is required.', 'url' => route('business-settings') . '#medicaid']);
        }

        if (empty($claim->getClientMedicaidId())) {
            $errors->push(['message' => 'Client Medicaid ID is required.', 'url' => $editClaimUrl]);
        }

        if (empty($claim->payer_code)) {
            $errors->push(['message' => 'Payer Code is required.', 'url' => $editClaimUrl]);
        }

        $claim->items->each(function (ClaimInvoiceItem $item) use (&$errors, $editClaimUrl) {
            if ($item->claimable_type != ClaimableService::class) {
                // Only services need to be validated.
                return;
            }

            /** @var ClaimableService $service */
            $service = $item->claimable;

            if (empty($service->service_code)) {
                $errors->push([
                    'message' => 'Service code is missing for service ' . $service->getDisplayName(),
                    'url' => $editClaimUrl
                ]);
            }
        });

        return $errors->isEmpty() ? null : $errors->toArray();
    }

    /**
     * Get all claim data to transmit, filtering out
     * and skipped items.
     *
     * @param ClaimInvoice $claim
     * @return array
     */
    public function getData(ClaimInvoice $claim): array
    {
        return $claim->items->map(function (ClaimInvoiceItem $item) {
            return $this->mapClaimableRecord($item);
        })
            ->filter()
            ->toArray();
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
     * @return null|array
     */
    abstract public function mapClaimableRecord(ClaimInvoiceItem $item): ?array;
}