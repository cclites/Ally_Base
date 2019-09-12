<?php

namespace App\Claims\Requests;

use App\Claims\ClaimRemitStatus;
use App\Http\Requests\FilteredResourceRequest;

class GetClaimRemitsRequest extends FilteredResourceRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'all' => 'required|in:true,false',
            'businesses' => 'nullable',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'reference' => 'nullable',
            'payer_id' => 'nullable',
            'type' => 'nullable',
            'status' => 'nullable',
        ];
    }

    /**
     * Get the filtered validated data.
     *
     * @return array
     */
    public function filtered() : array
    {
        $data = $this->validated();

        list($data['start_date'], $data['end_date']) = $this->filterDateRange();

        if ($key = ClaimRemitStatus::search($data['status'])) {
            $data['status'] = ClaimRemitStatus::$key();
        } else {
            $data['status'] = null;
        }

        $data['all'] = $data['all'] == 'true';

        return $data;
    }
}
