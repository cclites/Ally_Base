<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\FilteredResourceRequest;

/**
 * Class ChargesVsDepositsReportRequest
 * @package App\Http\Requests\Admin
 */
class ChargesVsDepositsReportRequest extends FilteredResourceRequest
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