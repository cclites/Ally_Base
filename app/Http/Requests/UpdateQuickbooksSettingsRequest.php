<?php

namespace App\Http\Requests;

use App\QuickbooksConnection;
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
            'name_format' => 'required|in:' . join(',', [QuickbooksConnection::NAME_FORMAT_LAST_FIRST, QuickbooksConnection::NAME_FORMAT_FIRST_LAST]),
            'fee_type_lead_agency' => 'required|in:' . join(',', [QuickbooksConnection::FEE_TYPE_REGISTRY, QuickbooksConnection::FEE_TYPE_CLIENT]),
            'fee_type_ltci' => 'required|in:' . join(',', [QuickbooksConnection::FEE_TYPE_REGISTRY, QuickbooksConnection::FEE_TYPE_CLIENT]),
            'fee_type_medicaid' => 'required|in:' . join(',', [QuickbooksConnection::FEE_TYPE_REGISTRY, QuickbooksConnection::FEE_TYPE_CLIENT]),
            'fee_type_private_pay' => 'required|in:' . join(',', [QuickbooksConnection::FEE_TYPE_REGISTRY, QuickbooksConnection::FEE_TYPE_CLIENT]),
            'fee_type_va' => 'required|in:' . join(',', [QuickbooksConnection::FEE_TYPE_REGISTRY, QuickbooksConnection::FEE_TYPE_CLIENT]),
            'mileage_service_id' => 'nullable|exists:quickbooks_services,id',
            'refund_service_id' => 'nullable|exists:quickbooks_services,id',
            'shift_service_id' => 'nullable|exists:quickbooks_services,id',
            'expense_service_id' => 'nullable|exists:quickbooks_services,id',
            'adjustment_service_id' => 'nullable|exists:quickbooks_services,id',
            'allow_shift_overrides' => 'boolean',
        ];
    }
}
