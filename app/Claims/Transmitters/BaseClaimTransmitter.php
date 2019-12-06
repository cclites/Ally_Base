<?php

namespace App\Claims\Transmitters;

use App\Claims\ClaimableService;
use App\Claims\Exceptions\ClaimTransmissionException;
use App\Claims\Contracts\ClaimTransmitterInterface;
use App\Claims\ClaimInvoiceItem;
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
        $errors = collect([]);
        $editClaimUrl = route('business.claims.edit', ['claim' => $claim]);

        if ($claim->items()->count() === 0) {
            throw new ClaimTransmissionException('You cannot transmit this Claim because there are no claimable items attached.');
        }

        if (empty($claim->business->ein)) {
            $errors->push(['message' => 'Your business EIN # is required.', 'url' => route('business-settings').'#medicaid']);
        }

        if (empty($claim->getClientMedicaidId())) {
            $errors->push(['message' => 'Client Medicaid ID is required.', 'url' => $editClaimUrl]);
        }

        if (empty($claim->payer_code)) {
            $errors->push(['message' => 'Payer Code is required.', 'url' => $editClaimUrl]);
        }

        foreach ($claim->items as $item) {
            /** @var ClaimInvoiceItem $item */
            if ($item->claimable_type == ClaimableService::class) {
                /** @var ClaimableService $service */
                $service = $item->claimable;
                if (empty($service->service_code)) {
                    $errors->push(['message' => "Service '{$service->service_name}' on {$item->date->toDateString()} as no Service Code.", 'url' => $editClaimUrl]);
                }
            }
        }

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