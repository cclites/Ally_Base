<?php

namespace App\Claims\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Billing\ClaimService;
use App\Rules\ValidEnum;

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
            'payer_code' => 'nullable',
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

        return $data;
    }
}
