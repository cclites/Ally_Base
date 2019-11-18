<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuickbooksCustomerMappingRequest extends FormRequest
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
            'clients' => 'required|array',
            'clients.*.id' => 'required|exists:clients,id',
            'clients.*.quickbooks_customer_id' => 'nullable|exists:quickbooks_customers,id',
        ];
    }

    public function filtered()
    {
        $data = $this->validated();
        return $data;
    }
}
