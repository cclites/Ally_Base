<?php

namespace App\Http\Requests;

use App\Billing\Payments\PaymentMethodType;
use App\ClientType;
use App\Rules\ValidEnum;

/**
 * Class PaymentSummaryReportRequest
 * @package App\Http\Requests
 */
class PaymentSummaryReportRequest extends FilteredResourceRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'businesses' => 'required|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'client_type' => 'nullable',
            'client' => 'nullable|numeric',
            'payment_method' => ['nullable', new ValidEnum(PaymentMethodType::class)],
        ];
    }

    public function messages()
    {
        return [
            'businesses.*' => 'You must select an Office Location.',
            'start_date.*' => 'Invalid start date.',
            'end_date.*' => 'Invalid start date.',
            'client_type.*' => 'Invalid Payer.',
            'client.*' => 'Invalid Client.',
            'payment_method.*' => 'Invalid Payment Type.',
        ];
    }
}
