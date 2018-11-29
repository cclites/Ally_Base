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
            'tags' => 'nullable|string',
            'body' => 'required|string',
        ];
    }
}
