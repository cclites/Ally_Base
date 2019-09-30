<?php

namespace App\Claims\Requests;

use App\Http\Requests\BusinessRequest;
use Illuminate\Validation\Rule;
use App\Claims\ClaimRemitType;
use App\Rules\ValidEnum;

class CreateClaimRemitRequest extends BusinessRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'date' => 'required|date',
            'payment_type' => ['required', new ValidEnum(ClaimRemitType::class)],
            'payer_id' => ['nullable',
                Rule::exists('payers', 'id')->where(function ($query) {
                    $query->where('chain_id', $this->getChain()->id);
                }),
            ],
            'reference' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0|max:999999.99',
            'notes' => 'nullable|string|max:255',
        ];

        return $rules;
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        $messages = [
            'amount.*' => 'Remit amount must have a value.',
        ];

        return array_merge(parent::messages(), $messages);
    }

    /**
     * Get the filtered validated data.
     *
     * @return array
     */
    public function filtered()
    {
        $data = $this->validated();

        $data['date'] = filter_date($data['date']);

        return $data;
    }
}
