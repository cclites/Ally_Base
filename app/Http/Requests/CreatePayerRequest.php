<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Payer;

class CreatePayerRequest extends FormRequest
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
            'name' => 'required|string',
            'npi_number' => 'nullable|string',
            'rates' => 'nullable|array',
            'rates.*.service_id' => 'nullable|numeric', // TODO: validate business chain service
            'rates.*.fixed_rate' => 'required|numeric|between:0,999.99',
            'rates.*.hourly_rate' => 'required|numeric|between:0,999.99',
            'rates.*.effective_start' => 'required|date',
            'rates.*.effective_end' => 'required|date', //|after_or_equal:' . $this->input('rates.*.effective_start'),
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
        $data['chain_id'] = auth()->user()->officeUser->chain_id;
        if (isset($data['rates'])) {
            $data['rates'] = collect($data['rates'])->map(function ($rate) {
                unset($rate['service']);
                return array_merge($rate, [
                    'effective_start' => utc_date($rate['effective_start'], 'Y-m-d H:i:s', null),
                    'effective_end' => utc_date($rate['effective_end'], 'Y-m-d H:i:s', null),
                ]);
            })->toArray();
        } else {
            $data['rates'] = [];
        }
        return $data;
    }
}
