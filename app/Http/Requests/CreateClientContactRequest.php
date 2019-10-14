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
            'work_phone' => 'nullable|max:45',
            'fax_number' => 'nullable|max:45',
            'address' => 'nullable|max:255',
            'city' => 'nullable|max:45',
            'state' => 'nullable|max:45',
            'zip' => 'nullable|max:45',
            'is_emergency' => 'nullable|boolean',
            'is_payer' => 'nullable|boolean',
            'has_poa' => 'nullable|boolean',
            'has_login_access' => 'nullable|boolean',
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
        $data['has_poa'] = $data['has_poa'] ? 1 : 0;
        $data['is_payer'] = $data['is_payer'] ? 1 : 0;
        $data['has_login_access'] = $data['has_login_access'] ? 1 : 0;
        
        if ( !in_array( $data['relationship'], [ 'custom', 'family' ] )) {
            $data['relationship_custom'] = null;
        }
        if ( $data['relationship'] == 'poa' ) {
            $data['has_poa'] = 1;
        }

        if (! $data['is_emergency']) {
            $data['emergency_priority'] = null;
        }
        
        return $data;
    }
}
