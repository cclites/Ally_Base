<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessOvertimeRequest extends BusinessRequest
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
            'ot_multiplier' => 'required|in:1.0,1.5,2.0',
            'ot_behavior' => 'nullable|in:caregiver,provider,both',
            'hol_multiplier' => 'required|in:1.0,1.5,2.0',
            'hol_behavior' => 'nullable|in:caregiver,provider,both',
        ];
    }

    /**
     * Get the filtered data.
     *
     * @return array
     */
    public function filtered()
    {
        $data = $this->validated();
        unset($data['business_id']);
        return $data;
    }
}
