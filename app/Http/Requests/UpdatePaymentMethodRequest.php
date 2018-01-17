<?php

namespace App\Http\Requests;

use App\Client;
use App\Rules\CreditCardValid;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $existing = $this->user()->role->getPaymentMethod($this->type === 'backup');
        if ($existing) {
            return $existing->user_id == $this->user()->id;
        } else {
            return true;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $existing = $this->user()->role->getPaymentMethod($this->type === 'backup');

        if ($this->has('number')) {
            return $this->creditCardRules($existing);
        } else if ($this->has('account_number')) {
            return $this->bankAccountRules($existing);
        }
    }

    private function bankAccountRules($existing)
    {
        $rules = [
            'nickname' => 'nullable',
            'account_type' => 'required|in:checking,savings',
            'account_holder_type' => 'required|in:business,personal',
            'name_on_account' => 'required',
        ];

        if (!$existing || substr($this->account_number, 0, 1) !== '*' || $this->routing_number !== '*********' ||
            substr($existing->account_number, -4) !== substr($this->account_number, -4)
        ) {
            $rules += [
                'account_number' => 'required|numeric|confirmed',
                'routing_number' => 'required|numeric|digits:9|confirmed',
            ];
        }
        return $rules;
    }

    private function creditCardRules($existing)
    {
        $rules = [
            'nickname' => 'nullable',
            'expiration_month' => 'required|digits_between:1,2',
            'expiration_year' => 'required|size:4',
            'name_on_card' => 'required'
        ];

        if (get_class($this->user()->role) == Client::class) {
            if (!$existing || strpos($this->number, '****') !== 0) {
                $rules += [
                    'number' => ['required', new CreditCardValid()],
                    'cvv' => 'required|digits_between:3,4'
                ];
            }
        }
        return $rules;
    }
}
