<?php
namespace App\Http\Requests;

use App\Rules\PhonePossible;
use Illuminate\Foundation\Http\FormRequest;
use App\ReferralSource;

class UpdateReferralSourceRequest extends FormRequest
{
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
            'organization' => 'nullable',
            'contact_name' => 'nullable',
            'phone' => ['required', new PhonePossible()],
            'contact_address_street' => 'required|string|max:150',
            'contact_address_street2' => 'nullable|string|max:150',
            'contact_address_city' => 'required|string|max:150',
            'contact_address_state' => 'required|string|max:150',
            'contact_address_zip' => 'required|string|digits:5',
            'active' => 'boolean',
            'type' => 'required|in:'. join(',', ReferralSource::validTypes()),

            'is_company' => 'boolean',
            'source_owner' => 'nullable|max:150',
            'source_type' => 'nullable|max:150',
            'web_address' => 'nullable|max:150',
            'work_phone' => 'nullable|max:15'
        ];
    }
}
