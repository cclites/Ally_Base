<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\ClientContact;

class CreateClientContactRequest extends FormRequest
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
            'name' => 'required|max:255',
            'relationship' => 'required|in:'.join(',', ClientContact::validRelationships()),
            'relationship_custom' => 'nullable|max:255',
            'email' => 'nullable|email|max:255',
            'phone1' => 'nullable|max:45',
            'phone2' => 'nullable|max:45',
            'address' => 'nullable|max:255',
            'city' => 'nullable|max:45',
            'state' => 'nullable|max:45',
            'zip' => 'nullable|max:45',
            'is_emergency' => 'nullable|boolean',
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
        
        $data['is_emergency'] = $data['is_emergency'] ? 1 : 0;
        
        if ($data['relationship'] != 'custom') {
            $data['relationship_custom'] = null;
        }

        if (! $data['is_emergency']) {
            $data['emergency_priority'] = null;
        }
        
        return $data;
    }
}
