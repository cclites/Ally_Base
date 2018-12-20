<?php
namespace App\Http\Requests;

class CreateNoteRequest extends BusinessRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'caregiver_id' => 'nullable|exists:caregivers,id',
            'client_id' => 'nullable|exists:clients,id',
            'prospect_id' => 'nullable|exists:prospects,id',
            'referral_source_id' => 'nullable|exists:referral_sources,id',
            'tags' => 'nullable|string',
            'type' => 'nullable|string',
            'body' => 'required|string',
        ];
    }
}
