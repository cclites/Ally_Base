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
        return [
            'new_application' => 'required|array',
            'new_application.sms' => 'boolean',
            'new_application.email' => 'boolean',
            'new_application.system' => 'boolean',
        ];
    }

    // protected function getValidatorInstance() {
    //     $validator = parent::getValidatorInstance();
    
    //     $validator->sometimes('notification_email', 'required|email', function ($input) {
    //         return $input->allow_email_notifications == 1;
    //     });
    
    //     return $validator;
    // }
}
