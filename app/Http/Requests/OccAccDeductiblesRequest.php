<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OccAccDeductiblesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return is_office_user() || is_admin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            '*.caregiver_id' => 'required|exists:caregivers,id',
            '*.amount'       => 'required',
            '*.start_date'   => 'required',
            '*.end_date'     => 'required',
            '*.businesses'   => 'nullable'
        ];
    }
}
