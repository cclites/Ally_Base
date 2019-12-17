<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if(auth()->user()->role_type === 'admin'){
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'medicaid_1099_default' => 'required|string|in:choose,yes,no',
            'private_pay_1099_default' => 'required|string|in:choose,yes,no',
            'other_1099_default' => 'required|string|in:choose,yes,no',

            'medicaid_1099_from' => 'required|string|in:ally,client',
            'private_pay_1099_from' => 'required|string|in:ally,client',
            'other_1099_from' => 'required|string|in:ally,client',

            'medicaid_1099_edit' => 'required|boolean',
            'private_pay_1099_edit' => 'required|boolean',
            'other_1099_edit' => 'required|boolean',
        ];
    }
}