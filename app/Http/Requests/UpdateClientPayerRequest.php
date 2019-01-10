<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientPayerRequest extends CreateClientPayerRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // redefine payer_id validation to add the ignore id when updating
        return array_merge(parent::rules(), [
            'payer_id' => [
                'nullable',
                Rule::exists('payers', 'id')->where(function ($query) {
                    $query->where('chain_id', $this->route('client')->business->chain_id);
                }),
                Rule::unique('client_payers')->ignore($this->route('payer')->id)->where(function ($query) {
                    $query->where('client_id', request()->client->id);
                }),
            ],
        ]);
    }
}
