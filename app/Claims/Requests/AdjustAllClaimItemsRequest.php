<?php

namespace App\Claims\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Claims\ClaimAdjustmentType;
use App\Rules\ValidEnum;

class AdjustAllClaimItemsRequest extends FormRequest
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
            'amount_applied' => 'required|numeric|min:-100|max:100',
            'adjustment_type' => ['required', new ValidEnum(ClaimAdjustmentType::class)],
            'note' => 'nullable|string|max:255',
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
            'amount_applied.*' => 'You can only adjust a percentage between 0 and 100.',
            'adjustment_type.*' => 'Adjustment type field is required.',
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

        return $data;
    }
}
