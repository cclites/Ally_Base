<?php

namespace App\Claims\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Claims\ClaimAdjustmentType;
use Illuminate\Validation\Rule;
use App\Rules\ValidEnum;

class CreateClaimAdjustmentRequest extends FormRequest
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
        return [
            'adjustments' => 'required|array',
            'adjustments.*.amount_applied' => 'required|numeric|not_in:0|min:-9999999.99|max:9999999.99',
            'adjustments.*.adjustment_type' => ['required', new ValidEnum(ClaimAdjustmentType::class)],
            'adjustments.*.claim_invoice_item_id' => [
                'required',
                Rule::exists('claim_invoice_items', 'id')
                    ->where(function ($query) {
                        $query->where('claim_invoice_id', $this->route('claim')->id);
                    })],
            'adjustments.*.note' => 'nullable|string|max:255',
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'adjustments.required' => 'You have not selected an amount to Adjust.',
            'adjustments.*.amount_applied.*' => 'Amount to Adjust field is required for all selected items.',
            'adjustments.*.adjustment_type.*' => 'Adjustment type field is required for all selected items.',
            'adjustments.*.claim_invoice_item_id.*' => 'Invalid claim item, please refresh the page and try again.',
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

        $data['adjustments'] = collect($data['adjustments'])->map(function (array $adjustment) {
            $adjustment['amount_applied'] = multiply(floatval(-1), floatval($adjustment['amount_applied']));
            return $adjustment;
        })->toArray();

        return $data;
    }
}
