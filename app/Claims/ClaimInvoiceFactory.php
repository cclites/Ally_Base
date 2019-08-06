<?php

namespace App\Claims;

use App\Address;
use App\Billing\ClaimStatus;
use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Invoiceable\ShiftService;
use App\Billing\InvoiceableType;
use App\Billing\Service;
use App\Caregiver;
use App\Shift;

class ClaimInvoiceFactory
{
    /**
     * Create a ClaimInvoice from a ClientInvoice.
     *
     * @param ClientInvoice $invoice
     * @return ClaimInvoice
     * @throws \Exception
     */
    public function createFromClientInvoice(ClientInvoice $invoice) : ClaimInvoice
    {
        $invoice->load('items', 'items.shift', 'items.shiftService', 'items.shiftService.shift');
        $client = $invoice->client;
        $business = $invoice->client->business;
        $payer = $invoice->clientPayer->payer;

        \DB::beginTransaction();
        /** @var ClaimInvoice $claim */
        $claim = ClaimInvoice::create([
            'business_id' => $business->id,
            'client_invoice_id' => $invoice->id,
            'name' => $this->getInvoiceName($business->id),

            // TODO: calculate from items
            'amount' => $invoice->amount,
            'amount_due' => $invoice->amount,

            'status' => ClaimStatus::CREATED(),
            'transmission_method' => $payer->getTransmissionMethod(),

            'client_id' => $client->id,
            'client_first_name' => $client->first_name,
            'client_last_name' => $client->last_name,
            'client_dob' => $client->date_of_birth,
            'client_medicaid_id' => $client->medicaid_id,
            'client_medicaid_diagnosis_codes' => $client->medicaid_diagnosis_codes,

            'payer_id' => $payer->id,
            'payer_name' => $payer->name,
            'payer_code' => $invoice->getPayerCode(),
            'plan_code' => $invoice->getPlanCode(),
        ]);

        $items = $invoice->items->map(function (ClientInvoiceItem $item) {
            switch ($item->invoiceable_type) {
                case InvoiceableType::SHIFT():
                    return $this->convertShift($item);
                case InvoiceableType::SHIFT_SERVICE():
                    return $this->convertService($item);
                case InvoiceableType::SHIFT_ADJUSTMENT():
                case InvoiceableType::SHIFT_EXPENSE():
                default:
                    return null;
            }
        })->filter();

        $claim->items()->saveMany($items);
        \DB::commit();

        return $claim;
    }

    /**
     * Create a ClaimInvoiceItem from a Shift-based ClientInvoiceItem.
     *
     * @param ClientInvoiceItem $item
     * @return ClaimInvoiceItem
     */
    protected function convertShift(ClientInvoiceItem $item) : ClaimInvoiceItem
    {
        $shift = $item->getShift();
        $caregiver = $shift->caregiver;
        $evvAddress = $shift->address;
        /** @var Service $service */
        $service = $shift->service;

        if (empty($service)) {
            throw new \InvalidArgumentException('Shift has no related service.');
        }

        return $this->createClaimInvoiceItem($item, $shift, $service, $caregiver, $evvAddress);
    }

    /**
     * Create a ClaimInvoiceItem from a ShiftService-based ClientInvoiceItem.
     *
     * @param ClientInvoiceItem $item
     * @return ClaimInvoiceItem
     */
    protected function convertService(ClientInvoiceItem $item) : ClaimInvoiceItem
    {
        /** @var \App\Shift $shift */
        $shift = $item->shiftService->shift;
        $caregiver = $shift->caregiver;
        $evvAddress = $shift->address;
        /** @var ShiftService $shiftService */
        $shiftService = $item->shiftService;
        /** @var Service $service */
        $service = $item->shiftService->service;

        $claimItem = $this->createClaimInvoiceItem($item, $shift, $service, $caregiver, $evvAddress);

        // Update the visit start and end times with the pro-rated versions for service breakouts.
        list($start, $end) = $shiftService->getStartAndEndTime();
        $claimItem->visit_start_time = $start;
        $claimItem->visit_end_time = $end;

        return $claimItem;
    }

    /**
     * Create a ClaimInvoiceItem from the given data.
     *
     * @param ClientInvoiceItem $item
     * @param Shift $shift
     * @param Service $service
     * @param Caregiver $caregiver
     * @param Address|null $evvAddress
     * @return ClaimInvoiceItem
     */
    protected function createClaimInvoiceItem(ClientInvoiceItem $item, Shift $shift, Service $service, Caregiver $caregiver, ?Address $evvAddress) : ClaimInvoiceItem
    {
        return ClaimInvoiceItem::make([
            'shift_id' => $shift->id,
            'claimable_id' => $shift->id,
            'claimable_type' => Shift::class,
            'amount' => $item->amount_due,
            'amount_due' => $item->amount_due,
            'rate' => $item->rate,
            'duration' => $item->units,
            'caregiver_id' => $caregiver->id,
            'caregiver_first_name' => $caregiver->first_name,
            'caregiver_last_name' => $caregiver->last_name,
            'caregiver_gender' => $caregiver->gender,
            'caregiver_dob' => $caregiver->date_of_birth,
            'caregiver_ssn' => $caregiver->ssn,
            'caregiver_medicaid_id' => $caregiver->medicaid_id,
            'address1' => optional($evvAddress)->address1,
            'address2' => optional($evvAddress)->address2,
            'city' => optional($evvAddress)->city,
            'state' => optional($evvAddress)->state,
            'zip' => optional($evvAddress)->zip,
            'latitude' => optional($evvAddress)->latitude,
            'longitude' => optional($evvAddress)->longitude,
            // All timestamps stored as UTC
            'scheduled_start_time' => $shift->scheduledStartTime(),
            'scheduled_end_time' => $shift->scheduledEndTime(),
            'visit_start_time' => $shift->checked_in_time,
            'visit_end_time' => $shift->checked_out_time,
            'evv_start_time' => $shift->checked_in_time,
            'evv_end_time' => $shift->checked_out_time,
            'checked_in_number' => $shift->checked_in_number,
            'checked_out_number' => $shift->checked_out_number,
            'checked_in_latitude' => $shift->checked_in_latitude,
            'checked_out_latitude' => $shift->checked_out_latitude,
            'checked_in_longitude' => $shift->checked_in_longitude,
            'checked_out_longitude' => $shift->checked_out_longitude,
            'has_evv' => $this->checkShiftForFullEVV($shift),
            'evv_method_in' => $shift->checked_in_method,
            'evv_method_out' => $shift->checked_out_method,
            'service_id' => $service->id,
            'service_name' => $service->name,
            'service_code' => $service->code,
            'activities' => $shift->activities->implode('code', ','),
            'caregiver_comments' => $shift->caregiver_comments,
        ]);
    }

    /**
     * Check if a shift has EVV data set for both clock in and out.
     *
     * @param \App\Shift $shift
     * @return bool
     */
    protected function checkShiftForFullEVV(Shift $shift) : bool
    {
        if ($shift->checked_in_method == Shift::METHOD_TELEPHONY) {
            if (! filled($shift->checked_in_number)) {
                return false;
            }
        } else if ($shift->checked_in_method == Shift::METHOD_GEOLOCATION) {
            if (! filled($shift->checked_in_latitude) || ! filled($shift->checked_in_longitude)) {
                return false;
            }
        } else {
            return false;
        }

        if ($shift->checked_out_method == Shift::METHOD_TELEPHONY) {
            if (! filled($shift->checked_out_number)) {
                return false;
            }
        } else if ($shift->checked_out_method == Shift::METHOD_GEOLOCATION) {
            if (! filled($shift->checked_out_latitude) || ! filled($shift->checked_out_longitude)) {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * @param int $businessId
     * @return mixed|string
     */
    protected function getInvoiceName(int $businessId) : string
    {
        return ClaimInvoice::getNextName($businessId);
    }
}