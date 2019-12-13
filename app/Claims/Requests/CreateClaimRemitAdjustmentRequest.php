<?php

namespace App\Claims\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Claims\ClaimAdjustmentType;
use App\Rules\ValidEnum;

class CreateClaimRemitAdjustmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'amount_applied' => 'required|numeric|not_in:0|min:-9999999.99|max:9999999.99',
            'adjustment_type' => ['required', new ValidEnum(ClaimAdjustmentType::class)],
            'note' => 'nullable|string|max:255'
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
