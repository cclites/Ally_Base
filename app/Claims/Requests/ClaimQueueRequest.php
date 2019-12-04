<?php

namespace App\Claims\Requests;

use App\Http\Requests\FilteredResourceRequest;
use App\Claims\ClaimInvoiceType;
use App\Billing\ClaimStatus;
use App\Rules\ValidEnum;

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
            'balance' => 'nullable|in:has_balance,no_balance',
            'claim_status' => ['nullable', new ValidEnum(ClaimStatus::class)],
            'payer_id' => 'nullable',
            'client_type' => 'nullable',
            'inactive' => 'nullable|bool',
            'invoice_id' => 'nullable',
            'claim_type' => ['nullable', new ValidEnum(ClaimInvoiceType::class)],
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

        if (filled($data['claim_status'])) {
            $data['claim_status'] = ClaimStatus::fromValue($data['claim_status']);
        }

        if (filled($data['claim_type'])) {
            $data['claim_type'] = ClaimInvoiceType::fromValue($data['claim_type']);
        }

        return $data;
    }

    /**
     * Get the type of date search that should performed.
     * service = dates of service
     * invoice = date of invoice
     *
     * @return string
     */
    public function getDateSearchType() : string
    {
        if ($this->date_type == 'invoice') {
            return $this->date_type;
        }

        return 'service';
    }
}