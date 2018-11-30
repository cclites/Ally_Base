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
                Rule::unique('activities', 'code')->where('business_id', $this->getBusinessId())->ignore($this->route('activity')->id ?? null)
            ],
            'name' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'code.unique' => 'The code already exists',
            'code.*' => 'The code must be a number greater than 100',
        ];
    }
}