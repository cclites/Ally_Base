<?php

namespace App\Claims\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Claims\ClaimInvoiceItem;
use App\Claims\ClaimableExpense;
use App\Claims\ClaimableService;
use Carbon\Carbon;

class ClaimInvoiceItemResource extends Resource
{
    /**
     * @var ClaimInvoiceItem $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        if (! $this->resource->relationLoaded('clientInvoice')) {
            $this->resource->load('clientInvoice');
        }

        return [
            'amount' => $this->resource->amount,
            'amount_due' => $this->resource->amount_due,
            'amount_paid' => $this->resource->getAmountPaid(),
            'claim_invoice_id' => $this->resource->claim_invoice_id,
            'related_shift_id' => optional($this->resource->claimable)->shift_id,
            'claimable' => $this->mapClaimable(),
            'invoiceable' => $this->resource->invoiceable,
            'date' => optional($this->resource->date)->toDateTimeString(),
            'claimable_id' => $this->resource->claimable_id,
            'claimable_type' => $this->resource->claimable_type,
            'type' => $this->type,
            'id' => $this->resource->id,
            'invoiceable_id' => $this->resource->invoiceable_id,
            'invoiceable_type' => $this->resource->invoiceable_type,
            'rate' => number_format($this->resource->rate, 2),
            'units' => number_format($this->resource->units, 2),
            'summary' => $this->resource->claimable->getName(),
            'start_time' => optional($this->resource->claimable->getStartTime())->toDateTimeString(),
            'end_time' => optional($this->resource->claimable->getEndTime())->toDateTimeString(),
            'caregiver_name' => $this->resource->getCaregiverName(),
            'client_name' => $this->resource->getClientName(),

            'client_invoice_id' => optional($this->resource->clientInvoice)->id,
            'client_invoice_name' => optional($this->resource->clientInvoice)->name,
            'client_invoice_date' => optional(optional($this->resource->clientInvoice)->created_at)->toDateTimeString(),

            'client_id' => $this->resource->client_id,
            'client_first_name' => $this->resource->client_first_name,
            'client_last_name' => $this->resource->client_last_name,
            'client_dob' => Carbon::parse($this->resource->client_dob)->format('m/d/Y'),
            'client_medicaid_id' => $this->resource->client_medicaid_id,
            'client_medicaid_diagnosis_codes' => $this->resource->client_medicaid_diagnosis_codes,
            'client_case_manager' => $this->resource->client_case_manager,
            'client_program_number' => $this->resource->client_program_number,
            'client_cirts_number' => $this->resource->client_cirts_number,
            'client_ltci_policy_number' => $this->resource->client_ltci_policy_number,
            'client_ltci_claim_number' => $this->resource->client_ltci_claim_number,
            'client_hic' => $this->resource->client_hic,
            'client_invoice_notes' => $this->resource->client_invoice_notes,

            'caregiver_id' => $this->resource->caregiver_id,
            'caregiver_first_name' => $this->resource->caregiver_first_name,
            'caregiver_last_name' => $this->resource->caregiver_last_name,
            'caregiver_gender' => $this->resource->caregiver_gender,
            'caregiver_dob' => Carbon::parse($this->resource->caregiver_dob)->format('m/d/Y'),
            'caregiver_ssn' => filled($this->resource->caregiver_ssn) ? '***-**-****' : '',
            'caregiver_medicaid_id' => $this->resource->caregiver_medicaid_id,
        ];
    }

    /**
     * Map the Claimable object resource.
     *
     * @return ClaimableExpenseResource|ClaimableServiceResource
     */
    public function mapClaimable()
    {
        switch ($this->resource->claimable_type) {
            case ClaimableService::class:
                return new ClaimableServiceResource($this->resource->claimable);
            case ClaimableExpense::class:
                return new ClaimableExpenseResource($this->resource->claimable);
            default:
                throw new \InvalidArgumentException('Unknown claimable type.');
        }
    }
}
