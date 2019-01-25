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
    
        // set user to current logged in user (for use with user's profile)
        $user = auth()->user();

        // update the user object if the request is for 
        // the office user side updating a client or caregiver 
        if ($this->route('client')) {
            $user = $this->route('client')->user;
        }
        if ($this->route('caregiver')) {
            $user = $this->route('caregiver')->user;
        }

        foreach ($user->getAvailableNotifications() as $cls) {
            $rules[$cls::getKey()] = 'required|array';
            $rules[$cls::getKey().'.sms'] = 'boolean';
            $rules[$cls::getKey().'.email'] = 'boolean';

            if ($user->role_type == 'office_user') {
                $rules[$cls::getKey().'.system'] = 'boolean';
            }
        }

        return $rules;
    }
}
