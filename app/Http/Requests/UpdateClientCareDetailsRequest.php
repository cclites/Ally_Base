<?php

namespace App\Http\Requests;

use App\CareDetails;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientCareDetailsRequest extends FormRequest
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
            'height' => 'nullable|string',
            'weight' => 'nullable|string',
            'lives_alone' => 'nullable|boolean',
            'pets' => ['present', 'array', Rule::in(CareDetails::PETS)],
            'smoker' => 'nullable|boolean',
            'alcohol' => 'nullable|boolean',
            'incompetent' => 'nullable|boolean',
            'competency_level' => ['nullable', Rule::in(CareDetails::COMPETENCY_LEVELS)],
            'can_provide_direction' => 'nullable|boolean',
            'assist_medications' => 'nullable|boolean',
            'medication_overseer' => 'nullable|string',
            'allergies' => 'nullable|string',
            'pharmacy_name' => 'nullable|string',
            'pharmacy_number' => 'nullable|string',
            'safety_measures' => ['present', 'array', Rule::in(CareDetails::SAFETY)],
            'safety_instructions' => 'nullable|string',
            'mobility' => ['present', 'array', Rule::in(CareDetails::MOBILITY)],
            'mobility_instructions' => 'nullable|string',
            'toileting' => ['present', 'array', Rule::in(CareDetails::TOILETING)],
            'toileting_instructions' => 'nullable|string',
            'bathing' => ['present', 'array', Rule::in(CareDetails::BATHING)],
            'bathing_frequency' => 'nullable|string',
            'bathing_instructions' => 'nullable|string',
            'vision' => ['nullable', Rule::in(CareDetails::VISION)],
            'hearing' => ['nullable', Rule::in(CareDetails::HEARING)],
            'hearing_instructions' => 'nullable|string',
            'diet' => ['present', 'array', Rule::in(CareDetails::DIET)],
            'diet_likes' => 'nullable|string',
            'feeding_instructions' => 'nullable|string',
            'skin' => ['present', 'array', Rule::in(CareDetails::SKIN)],
            'skin_conditions' => 'nullable|string',
            'hair' => ['nullable', Rule::in(CareDetails::HAIR)],
            'hair_frequency' => 'nullable|string',
            'oral' => ['present', 'array', Rule::in(CareDetails::ORAL)],
            'shaving' => ['nullable', Rule::in(CareDetails::SHAVING)],
            'shaving_instructions' => 'nullable|string',
            'nails' => ['present', 'array', Rule::in(CareDetails::NAILS)],
            'dressing' => ['present', 'array', Rule::in(CareDetails::DRESSING)],
            'dressing_instructions' => 'nullable|string',
            'housekeeping' => ['present', 'array', Rule::in(CareDetails::HOUSEKEEPING)],
            'housekeeping_instructions' => 'nullable|string',
            'errands' => ['present', 'array', Rule::in(CareDetails::ERRANDS)],
            'supplies' => ['present', 'array', Rule::in(CareDetails::SUPPLIES)],
            'supplies_instructions' => 'nullable|string',
            'prognosis' => ['required', Rule::in(CareDetails::PROGNOSIS)],
            'functional' => ['required', 'array', Rule::in(CareDetails::FUNCTIONAL)],
            'functional_other' => 'nullable|string',
            'comments' => 'nullable|string',
            'instructions' => 'nullable|string',
            'mental_status' => ['present', 'array', Rule::in(CareDetails::MENTAL_STATUS)],
        ];
    }
}
