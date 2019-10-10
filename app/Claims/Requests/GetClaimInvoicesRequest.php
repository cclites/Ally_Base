<?php

namespace App\Claims\Requests;

use App\Http\Requests\FilteredResourceRequest;

class GetClaimInvoicesRequest extends FilteredResourceRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'businesses' => 'nullable',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'payer_id' => 'nullable',
            'client_id' => 'nullable',
            'claim_status' => 'nullable',
            'client_type' => 'nullable',
            'inactive' => 'nullable|bool',
            'invoice_id' => 'nullable',
        ];
    }

    /**
     * Get the filtered validated data.
     *
     * @return array
     */
    public function filtered(): array
    {
        $data = $this->validated();

        list($data['start_date'], $data['end_date']) = $this->filterDateRange();

        return $data;
    }
}
