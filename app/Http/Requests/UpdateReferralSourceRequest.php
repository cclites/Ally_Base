<?php
namespace App\Http\Requests;

class UpdateReferralSourceRequest extends BusinessRequest
{
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
        ];
    }
}
