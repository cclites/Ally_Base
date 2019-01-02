<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationOptionsRequest extends FormRequest
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
            'allow_sms_notifications' => 'required|boolean',
            'allow_email_notifications' => 'required|boolean',
            'allow_system_notifications' => 'required|boolean',
            'notification_email' => 'nullable',
            'notification_phone' => 'nullable',
        ];
    }

    protected function getValidatorInstance() {
        $validator = parent::getValidatorInstance();
    
        $validator->sometimes('notification_email', 'required|email', function ($input) {
            return $input->allow_email_notifications == 1;
        });
    
        return $validator;
    }
}
