<?php
namespace App\Billing\Claims;

use App\Billing\Claim;
use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Contracts\ClaimTransmitterInterface;
use App\Billing\Exceptions\ClaimTransmissionException;
use App\Billing\Invoiceable\ShiftService;
use App\Shift;
use Illuminate\Support\Collection;

abstract class BaseClaimTransmitter implements ClaimTransmitterInterface
{
    /**
     * Validate an invoice has all the required parameters to
     * be transmitted as a claim.
     *
     * @param \App\Billing\ClientInvoice $invoice
     * @return bool
     * @throws \App\Billing\Exceptions\ClaimTransmissionException
     */
    public function validateInvoice(ClientInvoice $invoice) : bool
    {
        if (empty($invoice->client->business->ein)) {
            throw new ClaimTransmissionException('You cannot submit a claim because you do not have an EIN set.  You can edit this information under Settings > General > Medicaid.');
        }

        if (empty($invoice->client->medicaid_id)) {
            throw new ClaimTransmissionException('You cannot submit a claim because the client does not have a Medicaid ID set.  You can edit this information under the Insurance & Service Auths section of the Client\'s profile.');
        }

        $invoiceableShifts = $invoice->items->where('invoiceable_type', 'shifts')
            ->count();

        $invoiceableServices = $invoice->items->where('invoiceable_type', 'shift_services')
            ->count();

        if ($invoiceableShifts === 0 && $invoiceableServices === 0) {
            throw new ClaimTransmissionException('You cannot create a claim because there are no shifts attached to this invoice.');
        }

        return true;
    }

    /**
     * Convert claim into import row data.
     *
     * @param \App\Billing\Claim $claim
     * @return array
     */
    protected function getData(Claim $claim) : array
    {
        $shifts = $this->getInvoicedShifts($claim->invoice)
            ->map(function (Shift $shift) use ($claim) {
                return $this->mapShiftRecord($claim, $shift);
            })
            ->toArray();

        $services = $this->getInvoicedServices($claim->invoice)
            ->map(function (ShiftService $service) use ($claim) {
                return $this->mapServiceRecord($claim, $service);
            })
            ->toArray();

        return array_merge($shifts, $services);
    }

    /**
     * Get the eligible shifts that are directly attached
     * to a client invoice.  (only ones with a balance due)
     *
     * @param ClientInvoice $invoice
     * @return Shift[]|\Illuminate\Database\Eloquent\Collection
     */
    protected function getInvoicedShifts(ClientInvoice $invoice) : ?iterable
    {
        $shiftLineItems = $invoice->items->where('invoiceable_type', 'shifts')
            ->where('amount_due', '>', 0.0)
            ->pluck('invoiceable_id');

        return Shift::whereIn('id', $shiftLineItems)
            ->get();
    }

    /**
     * Get the eligible shift services that are directly attached
     * to a client invoice.  (only ones with a balance due)
     *
     * @param ClientInvoice $invoice
     * @return ShiftService[]|\Illuminate\Database\Eloquent\Collection
     */
    protected function getInvoicedServices(ClientInvoice $invoice) : ?iterable
    {
        $serviceLineItems = $invoice->items->where('invoiceable_type', 'shift_services')
            ->where('amount_due', '>', 0.0)
            ->pluck('invoiceable_id');

        return ShiftService::with('shift')
            ->whereIn('id', $serviceLineItems)
            ->get();
    }

    /**
     * Check transmitter is in test mode.
     *
     * @param Claim $claim
     * @return bool
     */
    public function isTestMode(Claim $claim) : bool
    {
        return false;
    }

    /**
     * Create and return the Claim file/data that would be transmitted.
     *
     * @param Claim $claim
     * @return null|string
     * @throws \Exception
     */
    public function test(Claim $claim) : ?string
    {
        return null;
    }

    /**
     * Submit the claim using the service.
     *
     * @param Claim $claim
     * @return bool
     * @throws \App\Billing\Exceptions\ClaimTransmissionException
     */
    abstract public function send(Claim $claim) : bool;

    /**
     * Map a claim's shift into importable data for the service.
     *
     * @param \App\Billing\Claim $claim
     * @param \App\Shift $shift
     * @return array
     */
    abstract public function mapShiftRecord(Claim $claim, Shift $shift) : array;

    /**
     * Map a claim's shift into importable data for the service.
     *
     * @param \App\Billing\Claim $claim
     * @param ShiftService $shiftService
     * @return array
     */
    abstract public function mapServiceRecord(Claim $claim, ShiftService $shiftService) : array;
}