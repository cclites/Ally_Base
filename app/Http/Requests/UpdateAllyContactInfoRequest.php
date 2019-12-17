<?php


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAllyContactInfoRequest extends FormRequest
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
            'company_name' => 'required|string',
            'company_address1' => 'required|string',
            'company_address2' => 'nullable|string',
            'company_city' => 'required|string',
            'company_state' => 'required|string',
            'company_zip' => 'required|string',
            'company_contact_name' => 'required|string',
            'company_contact_phone' => 'required|string',
            'company_contact_email' => 'required|string',
            'company_ein' => 'required|string',
        ];
    }

}