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
            'certification_start' => 'required|date',
            'certification_end' => 'required|date',
            'medical_record_number' => 'required|string',
            'provider_number' => 'required|string',
            'supplies' => ['present', 'array', Rule::in(CareDetails::SUPPLIES)],
            'supplies_instructions' => 'nullable|string',
            'prognosis' => ['required', Rule::in(CareDetails::PROGNOSIS)],
            'functional' => ['required', 'array', Rule::in(CareDetails::FUNCTIONAL)],
            'functional_other' => 'nullable|string',
            'principal_diagnosis_icd_cd' => 'nullable|string',
            'principal_diagnosis' => 'nullable|string',
            'principal_diagnosis_date' => 'nullable|date',
            'surgical_procedure_icd_cd' => 'nullable|string',
            'surgical_procedure' => 'nullable|string',
            'surgical_procedure_date' => 'nullable|date',
            'other_diagnosis_icd_cd' => 'nullable|string',
            'other_diagnosis' => 'nullable|string',
            'other_diagnosis_date' => 'nullable|date',
            'safety_instructions' => 'nullable|string',
            'mobility' => ['present', 'array', Rule::in(CareDetails::MOBILITY)],
            'mobility_instructions' => 'nullable|string',
            'physician_name' => 'required|string',
            'physiscian_address' => 'required|string',
            'physician_phone' => 'required|phone',
            'competency_level' => ['nullable', Rule::in(CareDetails::COMPETENCY_LEVELS)],
            'mental_status' => ['nullable', Rule::in(CareDetails::MENTAL_STATUS)],
        ];
    }
}
