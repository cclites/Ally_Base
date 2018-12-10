<?php
namespace App\Http\Requests;

use App\BankAccount;

class UpdateBusinessBankRequest extends BusinessRequest
{

    public function getBankAccount(BankAccount $existing = null)
    {
        $data = $this->filtered();

        if ($existing && $existing->account_number == $this->input('account_number')) {
            $existing->fill($data);
            return $existing;
        }

        return new BankAccount($data);
    }

    /**
     * Global validation rules, more specific validation rules in getBankAccount
     *
     * @return array
     */
    public function rules()
    {
        return [
            'account_number' => 'required|numeric|confirmed',
            'routing_number' => 'required|numeric|digits:9|confirmed',
            'nickname' => 'nullable',
            'account_type' => 'required|in:checking,savings',
            'account_holder_type' => 'required|in:business,personal',
            'name_on_account' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'account_number.numeric' => 'You must enter the full account number.',
            'routing_number.numeric' => 'You must enter the full routing number.',
        ];
    }
}
