<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuickbooksSettingsRequest extends FormRequest
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
            'name_format' => 'required|in:first_last,last_first',
            'mileage_service_id' => 'nullable|exists:quickbooks_services,id',
            'refund_service_id' => 'nullable|exists:quickbooks_services,id',
            'shift_service_id' => 'nullable|exists:quickbooks_services,id',
            'expense_service_id' => 'nullable|exists:quickbooks_services,id',
            'adjustment_service_id' => 'nullable|exists:quickbooks_services,id',
            'allow_shift_overrides' => 'boolean',
        ];
    }
}
