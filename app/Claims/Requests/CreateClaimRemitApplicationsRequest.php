<?php

namespace App\Claims\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Auth\Access\Gate;
use App\Claims\ClaimAdjustmentType;
use App\Claims\ClaimInvoiceItem;
use App\Rules\ValidEnum;

class CreateClaimRemitApplicationsRequest extends FormRequest
{
    /**
     * Authorize the request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'applications' => 'required|array',
            'applications.*.is_interest' => 'required|boolean',
            'applications.*.amount_applied' => 'required|numeric|not_in:0|min:-9999999.99|max:9999999.99',
            'applications.*.adjustment_type' => ['required', new ValidEnum(ClaimAdjustmentType::class)],
            'applications.*.claim_invoice_item_id' => 'required_unless:applications.*.is_interest,true|exists:claim_invoice_items,id',
        ];

        return $rules;
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'applications.required' => 'You have not selected an amount to apply.',
            'applications.*.amount_applied.*' => 'Amount to apply field is required for all selected items.',
            'applications.*.adjustment_type.*d' => 'Adjustment type field is required for all selected items.',
            'applications.*.claim_invoice_item_id.*' => 'Invalid claim data, please refresh the page and try again.',
        ];
    }

    /**
     * Get the filtered validated data.
     *
     * @return array
     */
    public function filtered()
    {
        $data = $this->validated();

        // Validate that the auth user has access to all of the
        // claims referenced in the request, and set the proper
        // claim_invoice_id for each application.
        $data['applications'] = collect($data['applications'])->map(function ($application) {
            if (isset($application['claim_invoice_item_id'])) {
                $claimItem = ClaimInvoiceItem::with('claim')
                    ->find($application['claim_invoice_item_id']);

                app(Gate::class)->authorize('update', $claimItem->claim);

                // TODO: do we need this? clean this up
                $applied = floatval($application['amount_applied']);
                $due = floatval($claimItem->amount_due);
                if ($applied === floatval(0)) {
                    // cannot apply nothing
                } else if ($applied > 0) {
                    // positive
                    if ($applied > $due) {

                    }
                } else {
                    // negative
                }
                $application['claim_invoice_id'] = $claimItem->claim_invoice_id;
            }

            return $application;
        })->toArray();

        return $data;
    }
}
