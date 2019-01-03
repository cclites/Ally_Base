<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationPreferencesRequest extends FormRequest
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
        $rules = [];
    
        foreach (auth()->user()->getAvailableNotifications() as $cls) {
            $rules[$cls::getKey()] = 'required|array';
            $rules[$cls::getKey().'.sms'] = 'boolean';
            $rules[$cls::getKey().'.email'] = 'boolean';
            $rules[$cls::getKey().'.system'] = 'boolean';
        }

        return $rules;
    }
}
