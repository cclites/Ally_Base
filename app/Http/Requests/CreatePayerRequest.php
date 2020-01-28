<?php

namespace App\Http\Requests;

use App\Billing\Claim;
use App\Claims\ClaimService;
use App\Rules\ValidEnum;
use Illuminate\Foundation\Http\FormRequest;
use App\Payer;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Rules\PhonePossible;

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
            'week_start' => 'required|numeric|between:0,6',
            'address1' => 'nullable',
            'address2' => 'nullable',
            'city' => 'nullable',
            'state' => 'nullable',
            'zip' => 'nullable|min:5',
            'payment_method_type' => 'nullable|string|in:businesses',
            'email' => 'nullable|email',
            'phone_number' => ['nullable', new PhonePossible()],
            'fax_number' => ['nullable', new PhonePossible()],
            'rates' => 'nullable|array',
            'rates.*.service_id' => [
                'nullable',
                'numeric',
                Rule::exists('services', 'id')->where(function ($query) {
                    $query->where('chain_id', auth()->user()->officeUser->chain_id);
                })
            ],
            'rates.*.fixed_rate' => 'required|numeric|between:0,999.99',
            'rates.*.hourly_rate' => 'required|numeric|between:0,999.99',
            'rates.*.effective_start' => 'required|date',
            'rates.*.effective_end' => 'required|date', //|after_or_equal:' . $this->input('rates.*.effective_start'),
            'transmission_method' => ['nullable', new ValidEnum(ClaimService::class)],
            'payer_code' => 'nullable|string|max:255',
            'plan_code' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'invoice_format' => 'nullable|string|max:255',
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
