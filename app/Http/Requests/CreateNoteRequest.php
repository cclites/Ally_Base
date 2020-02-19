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
            'call_direction' => 'nullable|required_if:type,phone|in:inbound,outbound',
            'tags' => 'nullable|string',
            'type' => 'nullable|string',
            'title' => 'nullable|string|max:100',
            'body' => 'required|string',
            'template_id' => 'nullable|exists:note_templates,id'
        ];
    }
}
