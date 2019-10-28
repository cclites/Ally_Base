<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\FilteredResourceRequest;

/**
 * Class ClaimTransmissionsReportRequest
 * @package App\Claims\Requests\Admin
 */
class PaymentsVsDepositsReportRequest extends FilteredResourceRequest
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
        ];
    }
}