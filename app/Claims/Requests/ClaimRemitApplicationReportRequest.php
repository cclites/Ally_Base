<?php

namespace App\Claims\Requests;

use App\Http\Requests\FilteredResourceRequest;
use App\Claims\ClaimRemitType;
use App\Rules\ValidEnum;

/**
 * Class ClaimRemitApplicationReportRequest
 * @package App\Claims\Requests
 */
class ClaimRemitApplicationReportRequest extends FilteredResourceRequest
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
            'payer_id' => 'nullable|numeric',
            'type' => ['nullable', new ValidEnum(ClaimRemitType::class)],
        ];
    }

    public function messages()
    {
        return [
            'businesses.*' => 'You must select an Office Location.',
            'start_date.*' => 'Invalid start date.',
            'end_date.*' => 'Invalid start date.',
            'payer_id.*' => 'Invalid Payer.',
            'type.*' => 'Invalid Payment Type.',
        ];
    }
}