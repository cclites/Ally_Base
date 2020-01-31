<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientCareMatchRequest extends FormRequest
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
            'starts_at' => 'nullable|date',
            'duration' => 'nullable|integer|required_if:exclude_overtime,1',
            'matches_activities' => 'nullable|numeric', // should be a decimal representing the minimum percent match
//            'matches_preferences' => 'boolean',
            'matches_gender' => 'nullable|string',
            'matches_certification' => 'nullable|string',
            'matches_language' => 'nullable|string',
            'matches_days' => 'nullable|array',
            'matches_existing_assignments' => 'boolean',
            'exclude_overtime' => 'boolean',
            'radius' => 'nullable|numeric',
            'rating' => 'nullable|numeric',
            'smoking' => 'required|in:1,0,client',
            'ethnicity' => 'nullable|in:client,select',
            'ethnicities' => 'required_if:ethnicity,select|array',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'starts_at.*' => 'The start date and time are invalid.',
            'duration.*' => 'The start time and end time are required for overtime calculations.',
            'ethnicities.*' => 'You must select at least one ethnicity',
        ];
    }

    /**
     * Filter the request data for processing.
     *
     * @return array
     */
    public function filtered(): array
    {
        $data = $this->validated();

        return $data;
    }
}
