<?php
namespace App\Billing\Claims;

use App\Billing\Claim;
use App\Billing\ClientInvoice;
use App\Billing\Contracts\ClaimTransmitterInterface;
use App\Billing\Exceptions\ClaimTransmissionException;
use App\Billing\Invoiceable\ShiftService;
use App\Shift;

abstract class BaseClaimTransmitter implements ClaimTransmitterInterface
{
    /**
     * Validate an invoice has all the required parameters to
     * be transmitted as a claim.
     *
     * @param \App\Billing\ClientInvoice $invoice
     * @return null|array
     * @throws \App\Billing\Exceptions\ClaimTransmissionException
     */
    public function validateInvoice(ClientInvoice $invoice) : ?array
    {
        $errors = ['business' => [], 'payer' => [], 'client' => [], 'credentials' => []];
        if (empty($invoice->client->business->ein)) {
            array_push($errors['business'], 'ein');
        }

        if (empty($invoice->client->medicaid_id)) {
            array_push($errors['client'], 'medicaid_id');
        }

        $invoiceableShifts = $this->getInvoicedShiftsQuery($invoice)->count();
        $invoiceableServices = $this->getInvoicedServicesQuery($invoice)->count();
        if ($invoiceableShifts === 0 && $invoiceableServices === 0) {
            throw new ClaimTransmissionException('You cannot create a claim because there are no services attached to this invoice with a billable amount.');
        }

        return $errors;
    }

    /**
     * Convert claim into import row data.
     *
     * @param \App\Billing\Claim $claim
     * @return array
     */
    protected function getData(Claim $claim) : array
    {
        $shifts = $this->getInvoicedShiftsQuery($claim->invoice)
            ->get()
            ->map(function (Shift $shift) use ($claim) {
                return $this->mapShiftRecord($claim, $shift);
            })
            ->toArray();

        $services = $this->getInvoicedServicesQuery($claim->invoice)
            ->get()
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
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getInvoicedShiftsQuery(ClientInvoice $invoice) : \Illuminate\Database\Eloquent\Builder
    {
        $shiftLineItems = $invoice->items->where('invoiceable_type', 'shifts')
            ->where('amount_due', '>', 0.0)
            ->pluck('invoiceable_id');

        return Shift::whereIn('id', $shiftLineItems);
    }

    /**
     * Get the eligible shift services that are directly attached
     * to a client invoice.  (only ones with a balance due)
     *
     * @param ClientInvoice $invoice
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getInvoicedServicesQuery(ClientInvoice $invoice) : \Illuminate\Database\Eloquent\Builder
    {
        $serviceLineItems = $invoice->items->where('invoiceable_type', 'shift_services')
            ->where('amount_due', '>', 0.0)
            ->pluck('invoiceable_id');

        return ShiftService::with('shift')
            ->whereIn('id', $serviceLineItems);
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