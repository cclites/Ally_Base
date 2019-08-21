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
            'certification_start' => 'required|string',
            'certification_end' => 'required|string',
            'medical_record_number' => 'required|numeric',
            'provider_number' => 'required|string',
            'principal_diagnosis_icd_cm' => 'nullable|string',
            'principal_diagnosis' => 'nullable|string',
            'principal_diagnosis_date' => 'nullable|string',
            'surgical_procedure_icd_cm' => 'nullable|string',
            'surgical_procedure' => 'nullable|string',
            'surgical_procedure_date' => 'nullable|string',
            'other_diagnosis_icd_cm' => 'nullable|string',
            'other_diagnosis' => 'nullable|string',
            'other_diagnosis_date' => 'nullable|string',
            'orders' => 'nullable|string',
            'physician_name' => 'required|string',
            'physician_address' => 'required|string',
            'physician_phone' => 'required|string',
        ];
    }
}
