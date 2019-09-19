<?php

namespace App\Claims\Requests;

use App\Claims\ClaimRemitType;

class UpdateClaimRemitRequest extends CreateClaimRemitRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules['amount'] = 'required|numeric|min:'.$this->claim_remit->amount_applied.'|max:999999.99';

        return $rules;
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        $messages = [];

        $messages['amount.min'] = 'Remit amount cannot be less than what has already been applied.';

        // Order matters here because max must come before * (in parent)
        return array_merge($messages, parent::messages());
    }
}