<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientPreferencesRequest extends FormRequest
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
            'gender' => 'nullable|in:M,F',
            'license' => 'nullable|in:HHA,CNA',
            'language' => 'nullable|string|size:2',
            'minimum_rating' => 'nullable|integer',
            'smokes' => 'nullable|boolean',
            'pets_dogs' => 'nullable|boolean',
            'pets_cats' => 'nullable|boolean',
            'pets_birds' => 'nullable|boolean',
        ];
    }
}
