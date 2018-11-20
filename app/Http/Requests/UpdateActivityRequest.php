<?php
namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateActivityRequest extends BusinessRequest
{

    public function rules()
    {
        return [
            'code' => [
                'required',
                'integer',
                'min:100',
                Rule::unique('activities', 'code')->where('business_id', $this->getBusinessId())
            ],
            'name' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'code.*' => 'The code must be a number greater than 100',
        ];
    }
}