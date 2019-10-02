<?php

namespace App\Claims\Requests;

use App\Http\Requests\FilteredResourceRequest;

/**
 * Class ClaimQueueRequest
 * @package App\Claims\Requests
 */
class ClaimQueueRequest extends FilteredResourceRequest
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
            'invoice_type' => 'nullable',
            'claim_status' => 'nullable',
            'payer_id' => 'nullable',
            'client_type' => 'nullable',
            'inactive' => 'nullable|bool',
        ];
    }
}