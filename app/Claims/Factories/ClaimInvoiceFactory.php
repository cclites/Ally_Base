<?php

namespace App\Claims\Factories;

use App\Claims\Exceptions\CannotDeleteClaimInvoiceException;
use App\Billing\Invoiceable\ShiftExpense;
use App\Billing\Invoiceable\ShiftService;
use App\Billing\ClientInvoiceItem;
use Illuminate\Support\Collection;
use App\Billing\InvoiceableType;
use App\Claims\ClaimInvoiceItem;
use App\Claims\ClaimableExpense;
use App\Claims\ClaimableService;
use App\Claims\ClaimInvoiceType;
use App\Billing\ClientInvoice;
use App\Billing\ClientPayer;
use App\Billing\ClaimStatus;
use App\Claims\ClaimInvoice;
use App\Billing\Service;
use App\Billing\Payer;
use App\Address;
use App\Billing\VisitEditActionEnum;
use App\Billing\VisitEditReasonEnum;
use App\Shift;

class ClaimInvoiceFactory
{
    /**
     * @var Collection
     */
    protected $warnings;

    /**
     * Create a single claim from multiple client invoices.
     *
     * @param \Illuminate\Database\Eloquent\Collection $invoices
     * @return array
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function createFromClientInvoices(\Illuminate\Database\Eloquent\Collection $invoices) : array
    {
        $this->warnings = collect();

        $invoices->load([
            'items',
            'items.shift',
            'clientPayer',

            'items.shift.clientSignature',
            'items.shift.caregiverSignature',

            'items.shiftExpense.shift.caregiver',

            'items.shiftService',
            'items.shiftService.shift.clientSignature',
            'items.shiftService.shift.caregiverSignature',
        ]);

        $this->validate($invoices);

        $type = $this->getClaimType($invoices);
        /** @var \App\Client $client */
        $client = $type == ClaimInvoiceType::PAYER() ? null : $invoices->first()->client;
        /** @var \App\Business $business */
        $business = $invoices->first()->client->business;
        /** @var \App\Billing\ClientPayer $clientPayer */
        $clientPayer = $invoices->first()->clientPayer;
        /** @var \App\Billing\Payer $payer */
        $payer = $clientPayer->payer;

        \DB::beginTransaction();
        /** @var ClaimInvoice $claim */
        $claim = ClaimInvoice::create([
            'claim_invoice_type' => $type,
            'business_id' => $business->id,
            'client_id' => optional($client)->id,
            'payer_id' => $payer->id,
            'payer_name' => $payer->name,
            'payer_code' => $invoices->first()->getPayerCode(),
            'plan_code' => $invoices->first()->getPlanCode(),

            'name' => $this->getInvoiceName($business->id),
            'status' => ClaimStatus::CREATED(),
            'transmission_method' => $payer->getTransmissionMethod(),

            // this will get re-written from the updateBalances() call below
            'amount' => $invoices->sum('amount'),
            'amount_due' => $invoices->sum('amount'),
        ]);

        $claim->clientInvoices()->saveMany($invoices);

        $items = $invoices->map(function (ClientInvoice $invoice) use ($clientPayer) {
            return $invoice->items->map(function (ClientInvoiceItem $item) use ($clientPayer) {
                switch ($item->invoiceable_type) {
                    case InvoiceableType::SHIFT():
                        return $this->convertShift($item, $clientPayer);
                    case InvoiceableType::SHIFT_SERVICE():
                        return $this->convertService($item, $clientPayer);
                    case InvoiceableType::SHIFT_EXPENSE():
                        return $this->convertExpense($item, $clientPayer);
                    case InvoiceableType::SHIFT_ADJUSTMENT():
                        // Adjustments are not copied to Claim Invoices.
                    default:
                        return null;
                }
            })->filter();
        })->flatten(1);

        $claim->items()->saveMany($items);
        $claim->updateBalance();

        \DB::commit();

        return [$claim, $this->warnings];
    }

    /**
     * Get the type of claim based on a group of client invoices.
     *
     * @param \Illuminate\Database\Eloquent\Collection $invoices
     * @return ClaimInvoiceType
     */
    public function getClaimType(\Illuminate\Database\Eloquent\Collection $invoices) : ClaimInvoiceType
    {
        if ($invoices->count() === 1) {
            return ClaimInvoiceType::SINGLE();
        }

        $totalClients = $invoices->unique('client_id')->values()->count();
        if ($totalClients === 1) {
            return ClaimInvoiceType::CLIENT();
        } else {
            return ClaimInvoiceType::PAYER();
        }
    }

    /**
     * Validate a claim can be created claim from a group of client invoices.
     *
     * @param \Illuminate\Database\Eloquent\Collection $invoices
     * @throws \InvalidArgumentException
     */
    public function validate(\Illuminate\Database\Eloquent\Collection $invoices) : void
    {
        foreach ($invoices as $invoice) {
            // Cannot use null client payer (manual adjustments)
            if (empty($invoice->clientPayer)) {
                throw new \InvalidArgumentException('Invoice has no payer and cannot be used for a claim.');
            }

            // Cannot use invoices that have already been converted to claims
            if ($invoice->claimInvoices->count() > 0) {
                throw new \InvalidArgumentException("Invoice #{$invoice->name} is already attached to a claim.");
            }

            // Cannot use invoices that have not been paid
            if (! $invoice->is_paid) {
                throw new \InvalidArgumentException("Invoice #{$invoice->name} has not been paid.");
            }
        }

        // Fail if invoices belong to separate business locations
        $totalBusinesses = $invoices->unique(function ($invoice) {
            return $invoice->client->business_id;
        })->values()->count();

        if ($totalBusinesses > 1) {
            throw new \InvalidArgumentException('You can only group invoices for the same office location.');
        }

        // Fail if payers are not all the same
        $totalPayers = $invoices->unique(function ($invoice) {
            return optional($invoice->clientPayer)->payer_id;
        })->values()->count();

        if ($totalPayers > 1) {
            throw new \InvalidArgumentException('You can only group invoices for the same payer.');
        }

        // Fail if payers are all 'private pay' or 'offline' but there are different clients
        // (This is technically a different payer)
        $totalClients = $invoices->unique('client_id')->values()->count();
        if ($totalClients > 1 & in_array($invoices->first()->clientPayer->payer_id, [Payer::PRIVATE_PAY_ID, Payer::OFFLINE_PAY_ID])) {
            throw new \InvalidArgumentException('You can only group invoices for the same payer.');
        }
    }

    /**
     * Create a ClaimInvoice from a ClientInvoice.  Returns
     * a tuple of [claim, warnings]
     *
     * @param ClientInvoice $invoice
     * @return array
     * @throws \Exception
     */
    public function createFromClientInvoice(ClientInvoice $invoice): array
    {
        return $this->createFromClientInvoices(
            ClientInvoice::where('id', $invoice->id)->get()
        );
    }

    /**
     * Create a ClaimInvoiceItem from a Shift-based ClientInvoiceItem.
     *
     * @param ClientInvoiceItem $item
     * @param ClientPayer $clientPayer
     * @return null|ClaimInvoiceItem
     */
    protected function convertShift(ClientInvoiceItem $item, ClientPayer $clientPayer): ?ClaimInvoiceItem
    {
        $shift = $item->getShift();
        $client = $shift->client;
        $caregiver = $shift->caregiver;
        $evvAddress = $shift->address;
        if (empty($evvAddress)) {
            $evvAddress = $shift->client->evvAddress;
        }
        /** @var Service $service */
        $service = $shift->service;
        if (empty($service)) {
            $this->warnings->push("Shift ({$shift->id}) has no related service.");
            return null;
        }

        $claimableService = $this->createClaimableService($item, $shift, $service, $evvAddress);

        return ClaimInvoiceItem::make([
            'client_invoice_id' => $item->invoice_id,
            'invoiceable_id' => $shift->id,
            'invoiceable_type' => Shift::class,
            'claimable_id' => $claimableService->id,
            'claimable_type' => ClaimableService::class,

            'client_id' => $client->id,
            'client_first_name' => $client->first_name,
            'client_last_name' => $client->last_name,
            'client_dob' => $client->date_of_birth,
            'client_medicaid_id' => $client->medicaid_id,
            'client_medicaid_diagnosis_codes' => $client->medicaid_diagnosis_codes,
            'client_case_manager' => $client->case_manager,
            'client_ltci_policy_number' => $client->getPolicyNumber(),
            'client_ltci_claim_number' => $client->getClaimNumber(),
            'client_hic' => $client->hic,
            'client_program_number' => $clientPayer->program_number,
            'client_cirts_number' => $clientPayer->cirts_number,
            'client_invoice_notes' => $clientPayer->notes,
            'caregiver_id' => $caregiver->id,
            'caregiver_first_name' => $caregiver->first_name,
            'caregiver_last_name' => $caregiver->last_name,
            'caregiver_gender' => $caregiver->gender,
            'caregiver_dob' => $caregiver->date_of_birth,
            'caregiver_ssn' => $caregiver->ssn,
            'caregiver_medicaid_id' => $caregiver->medicaid_id,

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
     * @param ClientPayer $clientPayer
     * @return null|ClaimInvoiceItem
     */
    protected function convertService(ClientInvoiceItem $item, ClientPayer $clientPayer): ?ClaimInvoiceItem
    {
        /** @var \App\Shift $shift */
        if (empty($item->shiftService)) {
            // Original service DB entry no longer exists
            $this->warnings->push("Shift Service ({$item->invoiceable_id}) no longer exists.");
            return null;
        }
        $shift = $item->shiftService->shift;
        /** @var \App\Client $client */
        $client = $shift->client;
        $caregiver = $shift->caregiver;
        $evvAddress = $shift->address;
        if (empty($evvAddress)) {
            $evvAddress = $shift->client->evvAddress;
        }
        /** @var ShiftService $shiftService */
        $shiftService = $item->shiftService;
        /** @var Service $service */
        $service = $item->shiftService->service;
        $claimableService = $this->createClaimableService($item, $shift, $service, $evvAddress);

        $claimItem = ClaimInvoiceItem::make([
            'client_invoice_id' => $item->invoice_id,
            'invoiceable_id' => $shiftService->id,
            'invoiceable_type' => ShiftService::class,
            'claimable_id' => $claimableService->id,
            'claimable_type' => ClaimableService::class,

            'client_id' => $client->id,
            'client_first_name' => $client->first_name,
            'client_last_name' => $client->last_name,
            'client_dob' => $client->date_of_birth,
            'client_medicaid_id' => $client->medicaid_id,
            'client_medicaid_diagnosis_codes' => $client->medicaid_diagnosis_codes,
            'client_case_manager' => $client->case_manager,
            'client_ltci_policy_number' => $client->getPolicyNumber(),
            'client_ltci_claim_number' => $client->getClaimNumber(),
            'client_hic' => $client->hic,
            'client_program_number' => $clientPayer->program_number,
            'client_cirts_number' => $clientPayer->cirts_number,
            'client_invoice_notes' => $clientPayer->notes,
            'caregiver_id' => $caregiver->id,
            'caregiver_first_name' => $caregiver->first_name,
            'caregiver_last_name' => $caregiver->last_name,
            'caregiver_gender' => $caregiver->gender,
            'caregiver_dob' => $caregiver->date_of_birth,
            'caregiver_ssn' => $caregiver->ssn,
            'caregiver_medicaid_id' => $caregiver->medicaid_id,

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
     * @param ClientPayer $clientPayer
     * @return ClaimInvoiceItem
     */
    protected function convertExpense(ClientInvoiceItem $item, ClientPayer $clientPayer): ClaimInvoiceItem
    {
        /** @var ShiftExpense $shiftExpense */
        $shiftExpense = $item->shiftExpense;
        /** @var \App\Client $client */
        $client = $shiftExpense->shift->client;
        /** @var \App\Caregiver $caregiver */
        $caregiver = $shiftExpense->shift->caregiver;

        $claimableExpense = ClaimableExpense::create([
            'shift_id' => $shiftExpense->shift_id,
            'name' => $shiftExpense->name,
            'date' => $item->date,
            'notes' => $shiftExpense->notes,
        ]);

        return ClaimInvoiceItem::make([
            'client_invoice_id' => $item->invoice_id,
            'invoiceable_id' => $shiftExpense->id,
            'invoiceable_type' => ShiftExpense::class,
            'claimable_id' => $claimableExpense->id,
            'claimable_type' => ClaimableExpense::class,

            'client_id' => $client->id,
            'client_first_name' => $client->first_name,
            'client_last_name' => $client->last_name,
            'client_dob' => $client->date_of_birth,
            'client_medicaid_id' => $client->medicaid_id,
            'client_medicaid_diagnosis_codes' => $client->medicaid_diagnosis_codes,
            'client_case_manager' => $client->case_manager,
            'client_ltci_policy_number' => $client->getPolicyNumber(),
            'client_ltci_claim_number' => $client->getClaimNumber(),
            'client_hic' => $client->hic,
            'client_program_number' => $clientPayer->program_number,
            'client_cirts_number' => $clientPayer->cirts_number,
            'client_invoice_notes' => $clientPayer->notes,
            'caregiver_id' => $caregiver->id,
            'caregiver_first_name' => $caregiver->first_name,
            'caregiver_last_name' => $caregiver->last_name,
            'caregiver_gender' => $caregiver->gender,
            'caregiver_dob' => $caregiver->date_of_birth,
            'caregiver_ssn' => $caregiver->ssn,
            'caregiver_medicaid_id' => $caregiver->medicaid_id,

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
     * @param Address|null $evvAddress
     * @return ClaimableService
     */
    protected function createClaimableService(
        ClientInvoiceItem $item,
        Shift $shift,
        Service $service,
        ?Address $evvAddress
    ): ClaimableService {
        return ClaimableService::create([
            'shift_id' => $shift->id,
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
            'visit_start_time' => $shift->checked_in_time, // This may get overwritten with pro-rated service times
            'visit_end_time' => $shift->checked_out_time, // This may get overwritten with pro-rated service times
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
            'client_signature_id' => optional($shift->clientSignature)->id,
            'caregiver_signature_id' => optional($shift->caregiverSignature)->id,
            'is_overtime' => $shift->hours_type == 'default' ? false : true,
            'visit_edit_action' => $shift->verified ? $shift->visit_edit_action : VisitEditActionEnum::nonEvvDefault(),
            'visit_edit_reason' => $shift->verified ? $shift->visit_edit_reason : VisitEditReasonEnum::nonEvvDefault(),
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
            $claim->adjustments()->forceDelete();

            foreach ($claim->items as $item) {
                $item->claimable->delete();
                $item->delete();
            }

            // Remove pivot table entry
            \DB::table('claim_invoice_client_invoice')->where('claim_invoice_id', $claim->id)->delete();

            $claim->delete();

            \DB::commit();
        } catch (\Exception $ex) {
            \DB::rollBack();
            app('sentry')->captureException($ex);
            throw new CannotDeleteClaimInvoiceException('An unexpected error occurred.');
        }
    }

    /**
     * Remove a claim invoice item and related data from the system.
     *
     * @param ClaimInvoiceItem $item
     * @throws CannotDeleteClaimInvoiceException
     */
    public function deleteClaimInvoiceItem(ClaimInvoiceItem $item) : void
    {
        $claim = $item->claim;
        if ($claim->adjustments()->count() > 0) {
            throw new CannotDeleteClaimInvoiceException('This claim has had adjustments applied.');
        }

        try {
            \DB::transaction(function() use ($item, $claim) {
                $item->claimable->delete();
                $item->delete();

                $claim->updateBalance();
                $claim->markAsModified();
            });
        } catch (\Throwable $ex) {
            app('sentry')->captureException($ex);
        }
    }
}
