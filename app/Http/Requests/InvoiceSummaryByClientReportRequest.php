<?php

namespace App\Http\Requests;

use App\ClientType;

/**
 * Class InvoiceSummaryByClientReportRequest
 * @package App\Http\Requests
 */
class InvoiceSummaryByClientReportRequest extends FilteredResourceRequest
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
            'mode' => 'required|in:invoice,claim',
            'payer_id' => 'nullable|numeric',
            'client_id' => 'nullable|numeric',
            'client_type' => 'nullable|in:' . join(',', ClientType::all()),
        ];
    }

    public function messages()
    {
        return [
            'businesses.*' => 'You must select an Office Location.',
            'start_date.*' => 'Invalid start date.',
            'end_date.*' => 'Invalid start date.',
            'payer_id.*' => 'Invalid Payer.',
            'client_id.*' => 'Invalid Client.',
            'client_type.*' => 'Invalid Client Type.',
        ];
    }
}