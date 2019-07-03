<?php

namespace App\Http\Requests;

use Crypt;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMissingClaimsFieldsRequest extends FormRequest
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
            'business_ein' => 'sometimes|required|max:255',
            'business_zip' => 'sometimes|required|max:45',
            'business_medicaid_id' => 'sometimes|required|max:255',
            'business_medicaid_npi_number' => 'sometimes|required|max:45',
            'business_medicaid_npi_taxonomy' => 'sometimes|required|max:45',

            'client_medicaid_id' => 'sometimes|required|max:255',
            'client_medicaid_payer_id' => 'sometimes|required|max:255',
            'client_medicaid_plan_id' => 'sometimes|required|max:255',
            'client_medicaid_diagnosis_codes' => 'sometimes|required|max:255',
            'client_date_of_birth' => 'sometimes|required|date',

            'payer_payer_code' => 'sometimes|required|max:255',
            'payer_plan_code' => 'sometimes|required|max:255',

            'credentials_hha_username' => 'sometimes|required|max:255',
            'credentials_hha_password' => 'sometimes|required|max:255',
            'credentials_tellus_username' => 'sometimes|required|max:255',
            'credentials_tellus_password' => 'sometimes|required|max:255',
        ];
    }

    /**
     * Filter the request data for processing.
     *
     * @return array
     */
    public function filtered(): array
    {
        $data = $this->validated();

        if (isset($data['credentials_hha_password'])) {
            $data['credentials_hha_password'] = Crypt::encrypt($data['credentials_hha_password']);
        }

        if (isset($data['credentials_tellus_password'])) {
            $data['credentials_tellus_password'] = Crypt::encrypt($data['credentials_tellus_password']);
        }

        return $data;
    }
}
