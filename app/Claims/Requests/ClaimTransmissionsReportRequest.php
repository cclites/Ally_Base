<?php

namespace App\Claims\Requests;

use App\Http\Requests\FilteredResourceRequest;

/**
 * Class ClaimTransmissionsReportRequest
 * @package App\Claims\Requests
 */
class ClaimTransmissionsReportRequest extends FilteredResourceRequest
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
        ];
    }
}