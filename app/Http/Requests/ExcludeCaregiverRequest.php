<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\ClientExcludedCaregiver;

class ExcludeCaregiverRequest extends FormRequest
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
            'caregiver_id' => 'required|int',
            'effective_at' => 'nullable|date',
            'reason' => 'required|in:' . join(',', ClientExcludedCaregiver::exclusionReasons()),
            'note' => 'nullable|string',
        ];
    }

    /**
     * Get the filtered request data.
     *
     * @return array
     */
    public function filtered()
    {
        $data = $this->validated();
        $data['effective_at'] = filter_date($data['effective_at']);
        return $data;
    }
}
