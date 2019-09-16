<?php
namespace App\Http\Requests;

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
            'organization' => 'required',
            'contact_name' => 'required',
            'phone' => 'nullable|max:32',
            'active' => 'boolean',
            'type' => 'required|in:'. join(',', ReferralSource::validTypes()),
        ];
    }
}
