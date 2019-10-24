<?php

namespace App\Http\Requests;

/**
 * Class ClaimRemitApplicationReportRequest
 * @package App\Claims\Requests
 */
class InvoiceSummaryByClientTypeReportRequest extends FilteredResourceRequest
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
        ];
    }

    public function messages()
    {
        return [
            'businesses.*' => 'You must select an Office Location.',
            'start_date.*' => 'Invalid start date.',
            'end_date.*' => 'Invalid start date.',
        ];
    }
}