<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransmitClaimRequest extends FormRequest
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
            'service' => 'required|in:HHA,TELLUS',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'service.*' => 'You must choose a transmission service.',
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

        $data['service'] = strtoupper($data['service']);

        return $data;
    }
}
