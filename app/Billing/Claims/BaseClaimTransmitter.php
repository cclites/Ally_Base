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

        $invoiceableShiftIds = $invoice->items->where('invoiceable_type', 'shifts')
            ->pluck('invoiceable_id');

        $invoiceableServiceIds = $invoice->items->where('invoiceable_type', 'shift_services')
            ->pluck('invoiceable_id');

        $shiftCount = Shift::whereIn('id', $invoiceableShiftIds)->count();

        // check for split shifts by checking dupe invoiceable id
        $splitShiftsCount = ClientInvoiceItem::where('invoiceable_type', 'shifts')
            ->whereIn('invoiceable_id', $invoiceableShiftIds)
            ->select('invoiceable_id')
            ->groupBy('invoiceable_id')
            ->havingRaw('count(invoiceable_id) > 1')
            ->count();

        if ($splitShiftsCount > 0) {
            throw new ClaimTransmissionException('You cannot create a claim because of split shifts, you must contact Ally.');
        }

        if ($invoiceableServiceIds->count() > 0) {
            if ($this->checkForSplitServiceBreakoutShifts($invoice, $invoiceableServiceIds)) {
                throw new ClaimTransmissionException('You cannot create a claim because of split shifts, you must contact Ally.');
            }

            $shiftCount += ShiftService::whereIn('id', $invoiceableServiceIds) // add shifts via shift_services
                ->get()
                ->unique('shift_id')
                ->count();
        }

        if ($shiftCount === 0) {
            throw new ClaimTransmissionException('You cannot create a claim because there are no shifts attached to this invoice.');
        }

        return true;
    }

    /**
     * Check if the services of a shift are split on to
     * multiple invoices.
     *
     * @param ClientInvoice $invoice
     * @param Collection $shiftServiceIds
     * @return bool
     */
    protected function checkForSplitServiceBreakoutShifts(ClientInvoice $invoice, Collection $shiftServiceIds) : bool
    {
        // check if a shift service has been split
        $count = ClientInvoiceItem::where('invoiceable_type', 'shift_services')
            ->whereIn('invoiceable_id', $shiftServiceIds)
            ->select('invoiceable_id')
            ->groupBy('invoiceable_id')
            ->havingRaw('count(invoiceable_id) > 1')
            ->count();

        if ($count > 0) {
            return true;
        }

        $relatedIds = collect([]);
        $services = ShiftService::where('id', $shiftServiceIds)->get();
        foreach ($services as $service) {
            $relatedIds = $relatedIds->merge($service->shift->services->pluck('id'));
        }

        // check for an invoice item that belongs to a different invoice
        // that contains one of the related service IDs.
        $count = ClientInvoiceItem::where('invoiceable_type', 'shift_services')
            ->whereIn('invoiceable_id', $relatedIds->unique())
            ->where('invoice_id', '<>', $invoice->id)
            ->count();

        if ($count > 0) {
            return true;
        }

        return false;
    }

    /**
     * Convert claim into import row data.
     *
     * @param \App\Billing\Claim $claim
     * @return array
     */
    protected function getData(Claim $claim) : array
    {
        return $this->getInvoicedShifts($claim->invoice)
            ->merge($this->getShiftsFromInvoicedServices($claim->invoice))
            ->map(function ($shift) use ($claim) {
                return $this->mapShiftRecord($claim, $shift);
            })
            ->toArray();
    }

    /**
     * Get the shifts that are directly attached to a client invoice.
     *
     * @param ClientInvoice $invoice
     * @return Shift[]|\Illuminate\Database\Eloquent\Collection
     */
    protected function getInvoicedShifts(ClientInvoice $invoice)
    {
        $shiftLineItems = $invoice->items->where('invoiceable_type', 'shifts')
            ->pluck('invoiceable_id');

        return Shift::whereIn('id', $shiftLineItems)
            ->get();
    }

    /**
     * Get the parent shifts of the services that are directly
     * attached to a client invoice.
     *
     * @param ClientInvoice $invoice
     * @return Shift[]|\Illuminate\Database\Eloquent\Collection
     */
    protected function getShiftsFromInvoicedServices(ClientInvoice $invoice)
    {
        $serviceLineItems = $invoice->items->where('invoiceable_type', 'shift_services')
            ->pluck('invoiceable_id');

        $serviceShiftIds = ShiftService::whereIn('id', $serviceLineItems)
            ->get()
            ->unique('shift_id');

        return Shift::whereIn('id', $serviceShiftIds)->get();
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
}