<?php

namespace App\Claims\Requests;

use App\Http\Requests\FilteredResourceRequest;

/**
 * Class GetClientInvoicesRequest
 * @package App\Claims\Requests
 */
class GetClientInvoicesRequest extends FilteredResourceRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'client_id' => 'nullable|numeric',
            'businesses' => 'nullable',
            'invoice_type' => 'nullable|in:has_claim,no_claim',
            'payer_id' => 'nullable',
            'client_type' => 'nullable',
            'inactive' => 'nullable|bool',
            'invoice_id' => 'nullable',
        ];
    }
}