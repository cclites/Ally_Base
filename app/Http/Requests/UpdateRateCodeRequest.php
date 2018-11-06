<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRateCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                Rule::unique('rate_codes')->where(function ($query) {
                    $query->where('business_id', activeBusiness()->id);
                    if ($rateCode = $this->route('rate_code')) {
                        $query->where('id', '!=', $rateCode->id);
                    }
                })
            ],
            'type' => 'required|in:caregiver,client',
            'rate' => 'required|numeric|min:0|max:999.99',
            'fixed' => 'boolean',
        ];
    }
}
