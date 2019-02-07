<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class UpdateClientRatesRequest extends FormRequest
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
            'rates' => 'nullable|array',
            'rates.*.service_id' => [
                'nullable',
                'numeric',
                Rule::exists('services', 'id')->where(function ($query) {
                    $query->where('chain_id', $this->route('client')->business->chain_id);
                })
            ],
            'rates.*.payer_id' => [
                'nullable',
                'numeric',
                Rule::exists('payers', 'id')->where(function ($query) {
                    $query->where('chain_id', $this->route('client')->business->chain_id);
                })
            ],
            'rates.*.caregiver_id' => [
                'nullable',
                'numeric',
                'exists:caregivers,id'
                // Rule::exists('client_caregivers', 'caregiver_id')->where(function ($query) {
                //     $query->where('client_id', $this->route('client')->id);
                // })
            ],
            'rates.*.effective_start' => 'required|date',
            'rates.*.effective_end' => 'required|date', 
            'rates.*.caregiver_fixed_rate' => 'required|numeric|between:0,999.99',
            'rates.*.caregiver_hourly_rate' => 'required|numeric|between:0,999.99',
            'rates.*.client_fixed_rate' => 'required|numeric|between:0,999.99',
            'rates.*.client_hourly_rate' => 'required|numeric|between:0,999.99',
        ];
    }

    /**
     * Filter the request data for processing.
     *
     * @return array
     */
    public function filtered() : array
    {
        $data = $this->validated();
        if (isset($data['rates'])) {
            $data['rates'] = collect($data['rates'])->map(function ($rate) {
                return array_merge($rate, [
                    'effective_start' => (new Carbon($rate['effective_start']))->format('Y-m-d'),
                    'effective_end' => (new Carbon($rate['effective_end']))->format('Y-m-d'),
                ]);
            })->toArray();
        } else {
            $data['rates'] = [];
        }
        return $data;
    }
}
