<?php

namespace App\Http\Requests;

use App\CareDetails;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSkilledNursingPocRequest extends FormRequest
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
//            'height' => 'nullable|string',
//            'diet' => ['present', 'array', Rule::in(CareDetails::DIET)],
        ];
    }
}
