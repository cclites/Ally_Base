<?php

namespace App\Claims\Factories;

use App\Claims\ClaimInvoiceItem;
use App\Claims\Exceptions\CannotDeleteClaimInvoiceException;
use App\Billing\Invoiceable\ShiftExpense;
use App\Billing\Invoiceable\ShiftService;
use App\Billing\ClientInvoiceItem;
use App\Billing\InvoiceableType;
use App\Claims\ClaimableExpense;
use App\Claims\ClaimableService;
use App\Billing\ClientInvoice;
use App\Billing\ClaimStatus;
use App\Claims\ClaimInvoice;
use App\Billing\Service;
use App\Caregiver;
use App\Address;
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
    public function createFromClientInvoice(ClientInvoice $invoice): ClaimInvoice
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

            // this will get re-written from the updateBalances() call below
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
                case InvoiceableType::SHIFT_EXPENSE():
                    return $this->convertExpense($item);
                case InvoiceableType::SHIFT_ADJUSTMENT():
                    // Adjustments are not copied to Claim Invoices.
                default:
                    return null;
            }
        })->filter();

        $claim->items()->saveMany($items);

        $claim->updateBalances();

        \DB::commit();

        return $claim;
    }

    /**
     * Create a ClaimInvoiceItem from a Shift-based ClientInvoiceItem.
     *
     * @param ClientInvoiceItem $item
     * @return ClaimInvoiceItem
     */
    protected function convertShift(ClientInvoiceItem $item): ClaimInvoiceItem
    {
        $shift = $item->getShift();
        $caregiver = $shift->caregiver;
        $evvAddress = $shift->address;
        if (empty($evvAddress)) {
            $evvAddress = $shift->client->evvAddress;
        }
        /** @var Service $service */
        $service = $shift->service;
        if (empty($service)) {
            throw new \InvalidArgumentException('Shift has no related service.');
        }

        $claimableService = $this->createClaimableService($item, $shift, $service, $caregiver, $evvAddress);

        return ClaimInvoiceItem::make([
            'invoiceable_id' => $shift->id,
            'invoiceable_type' => Shift::class,
            'claimable_id' => $claimableService->id,
            'claimable_type' => ClaimableService::class,
            'rate' => $item->rate,
            'units' => $item->units,
            'amount' => $item->amount_due,
            'amount_due' => $item->amount_due,
            'date' => $claimableService->visit_start_time,
        ]);
    }

    /**
     * Create a ClaimInvoiceItem from a ShiftService-based ClientInvoiceItem.
     *
     * @param ClientInvoiceItem $item
     * @return ClaimInvoiceItem
     */
    protected function convertService(ClientInvoiceItem $item): ClaimInvoiceItem
    {
        /** @var \App\Shift $shift */
        $shift = $item->shiftService->shift;
        $caregiver = $shift->caregiver;
        $evvAddress = $shift->address;
        if (empty($evvAddress)) {
            $evvAddress = $shift->client->evvAddress;
        }
        /** @var ShiftService $shiftService */
        $shiftService = $item->shiftService;
        /** @var Service $service */
        $service = $item->shiftService->service;
        $claimableService = $this->createClaimableService($item, $shift, $service, $caregiver, $evvAddress);

        $claimItem = ClaimInvoiceItem::make([
            'invoiceable_id' => $shiftService->id,
            'invoiceable_type' => ShiftService::class,
            'claimable_id' => $claimableService->id,
            'claimable_type' => ClaimableService::class,
            'rate' => $item->rate,
            'units' => $item->units,
            'amount' => $item->amount_due,
            'amount_due' => $item->amount_due,
            'date' => $claimableService->visit_start_time,
        ]);

        // Update the visit start and end times with the pro-rated versions for service breakouts.
        list($start, $end) = $shiftService->getStartAndEndTime();
        $claimItem->claimable->update([
            'visit_start_time' => $start,
            'visit_end_time' => $end,
        ]);

        return $claimItem;
    }

    /**
     * Create a ClaimInvoiceItem from a ShiftExpense.
     *
     * @param ClientInvoiceItem $item
     * @return ClaimInvoiceItem
     */
    protected function convertExpense(ClientInvoiceItem $item): ClaimInvoiceItem
    {
        /** @var \App\ShiftExpense $shiftExpense */
        $shiftExpense = $item->shiftExpense;

        $claimableExpense = ClaimableExpense::create([
            'shift_id' => $shiftExpense->shift_id,
            'name' => $shiftExpense->name,
            'date' => $item->date,
            'notes' => $shiftExpense->notes,
        ]);

        return ClaimInvoiceItem::make([
            'invoiceable_id' => $shiftExpense->id,
            'invoiceable_type' => ShiftExpense::class,
            'claimable_id' => $claimableExpense->id,
            'claimable_type' => ClaimableExpense::class,
            'rate' => $item->rate,
            'units' => $item->units,
            'amount' => $item->amount_due,
            'amount_due' => $item->amount_due,
            'date' => $item->date,
        ]);
    }

    /**
     * Create a ClaimInvoiceItem from the given service data.
     *
     * @param ClientInvoiceItem $item
     * @param Shift $shift
     * @param Service $service
     * @param Caregiver $caregiver
     * @param Address|null $evvAddress
     * @return ClaimableService
     */
    protected function createClaimableService(ClientInvoiceItem $item, Shift $shift, Service $service, Caregiver $caregiver, ?Address $evvAddress): ClaimableService
    {
        return ClaimableService::create([
            'shift_id' => $shift->id,
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
            'evv_method_in' => $this->mapEvvMethod($shift->checked_in_method),
            'evv_method_out' => $this->mapEvvMethod($shift->checked_out_method),
            'service_id' => $service->id,
            'service_name' => $service->name,
            'service_code' => $service->code,
            'activities' => $shift->activities->implode('code', ','),
            'caregiver_comments' => $shift->caregiver_comments,
        ]);
    }

    /**
     * Map Shift clock in/out method to Claimable EVV method.
     *
     * @param string|null $method
     * @return string|null
     */
    protected function mapEvvMethod(?string $method): ?string
    {
        if ($method == Shift::METHOD_GEOLOCATION) {
            return ClaimableService::EVV_METHOD_GEOLOCATION;
        } elseif ($method == Shift::METHOD_TELEPHONY) {
            return ClaimableService::EVV_METHOD_TELEPHONY;
        }

        return null;
    }

    /**
     * Check if a shift has EVV data set for both clock in and out.
     *
     * @param \App\Shift $shift
     * @return bool
     */
    protected function checkShiftForFullEVV(Shift $shift): bool
    {
        if ($shift->checked_in_method == Shift::METHOD_TELEPHONY) {
            if (!filled($shift->checked_in_number)) {
                return false;
            }
        } elseif ($shift->checked_in_method == Shift::METHOD_GEOLOCATION) {
            if (!filled($shift->checked_in_latitude) || !filled($shift->checked_in_longitude)) {
                return false;
            }
        } else {
            return false;
        }

        if ($shift->checked_out_method == Shift::METHOD_TELEPHONY) {
            if (!filled($shift->checked_out_number)) {
                return false;
            }
        } elseif ($shift->checked_out_method == Shift::METHOD_GEOLOCATION) {
            if (!filled($shift->checked_out_latitude) || !filled($shift->checked_out_longitude)) {
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
    protected function getInvoiceName(int $businessId): string
    {
        return ClaimInvoice::getNextName($businessId);
    }

    /**
     * Delete a claim invoice permanently, along with all of it's associated data.
     *
     * @param ClaimInvoice $claim
     * @throws CannotDeleteClaimInvoiceException
     */
    public function deleteClaimInvoice(ClaimInvoice $claim): void
    {
        if ($claim->hasBeenTransmitted()) {
            throw new CannotDeleteClaimInvoiceException('This claim has already been transmitted.');
        }

        try {
            \DB::beginTransaction();

            foreach ($claim->items as $item) {
                $item->claimable->delete();
                $item->delete();
            }

            // TODO: this needs to also handle any AR payments and update remit balances

            $claim->delete();

            \DB::commit();
        } catch (\Exception $ex) {
            \DB::rollBack();
            app('sentry')->captureException($ex);
            throw new CannotDeleteClaimInvoiceException('An unexpected error occurred.');
        }
    }
}
