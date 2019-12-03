<?php

namespace App\Claims\Factories;

use App\Billing\Payer;
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
use App\Caregiver;
use App\Address;
use App\Client;
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

            'items.shift.client.caseManager',
            'items.shift.clientSignature',
            'items.shift.caregiverSignature',

            'items.shiftExpense.shift.caregiver',
            'items.shiftExpense.shift.client',

            'items.shiftService',
            'items.shiftService.shift.client.caseManager',
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
                        return $this->convertExpense($item);
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

        // Fail if payers are all 'private pay' but there are different clients
        // (This is technically
        $totalClients = $invoices->unique('client_id')->values()->count();
        if ($totalClients > 1 & $invoices->first()->clientPayer->payer_id === Payer::PRIVATE_PAY_ID) {
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

        $claimableService = $this->createClaimableService($item, $shift, $service, $client, $caregiver, $evvAddress, $clientPayer);

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
        $claimableService = $this->createClaimableService($item, $shift, $service, $client, $caregiver, $evvAddress, $clientPayer);

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
        /** @var ShiftExpense $shiftExpense */
        $shiftExpense = $item->shiftExpense;

        $claimableExpense = ClaimableExpense::create([
            'shift_id' => $shiftExpense->shift_id,
            'client_id' => $shiftExpense->shift->client->id,
            'client_first_name' => $shiftExpense->shift->client->first_name,
            'client_last_name' => $shiftExpense->shift->client->last_name,
            'caregiver_id' => $shiftExpense->shift->caregiver->id,
            'caregiver_first_name' => $shiftExpense->shift->caregiver->first_name,
            'caregiver_last_name' => $shiftExpense->shift->caregiver->last_name,
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
     * @param Client $client
     * @param Caregiver $caregiver
     * @param Address|null $evvAddress
     * @param ClientPayer $clientPayer
     * @return ClaimableService
     */
    protected function createClaimableService(
        ClientInvoiceItem $item,
        Shift $shift,
        Service $service,
        Client $client,
        Caregiver $caregiver,
        ?Address $evvAddress,
        ClientPayer $clientPayer
    ): ClaimableService {
        return ClaimableService::create([
            'shift_id' => $shift->id,
            'client_id' => $client->id,
            'client_first_name' => $client->first_name,
            'client_last_name' => $client->last_name,
            'client_dob' => $client->date_of_birth,
            'client_medicaid_id' => $client->medicaid_id,
            'client_medicaid_diagnosis_codes' => $client->medicaid_diagnosis_codes,
            'client_case_manager' => optional($client->caseManager)->name_last_first,
            'client_program_number' => $clientPayer->program_number,
            'client_cirts_number' => $clientPayer->cirts_number,
            'client_ltci_policy_number' => $client->getPolicyNumber(),
            'client_ltci_claim_number' => $client->getClaimNumber(),
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
            'client_signature_id' => optional($shift->clientSignature)->id,
            'caregiver_signature_id' => optional($shift->caregiverSignature)->id,
            'is_overtime' => $shift->hours_type == 'default' ? false : true,
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

            $claim->delete();

            \DB::commit();
        } catch (\Exception $ex) {
            \DB::rollBack();
            app('sentry')->captureException($ex);
            throw new CannotDeleteClaimInvoiceException('An unexpected error occurred.');
        }
    }
}
