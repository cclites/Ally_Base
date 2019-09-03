<?php

namespace App\Http\Requests;

use App\Billing\ClaimService;
use App\Rules\ValidEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClaimInvoiceRequest extends FormRequest
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
            'client_first_name' => 'required',
            'client_last_name' => 'required',
            'payer_code' => 'nullable',
            'client_medicaid_id' => 'nullable',
            'client_dob' => 'nullable|date',
            'client_medicaid_diagnosis_codes' => 'nullable',
            'plan_code' => 'nullable',
            'transmission_method' => ['nullable', new ValidEnum(ClaimService::class)],
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
        if ($data['client_dob']) $data['client_dob'] = filter_date($data['client_dob']);
        return $data;
    }
}
