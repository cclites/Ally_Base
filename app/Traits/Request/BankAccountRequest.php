<?php


namespace App\Traits\Request;


use App\BankAccount;
use Illuminate\Http\Request;

trait BankAccountRequest
{
    public function validateBankAccount(Request $request, $existing = null)
    {
        $rules = [
            'nickname' => 'nullable',
            'account_type' => 'required|in:checking,savings',
            'account_holder_type' => 'required|in:business,personal',
            'name_on_account' => 'required',
        ];

        if (!$existing
            || substr($request->input('account_number'), 0, 1) !== '*'
            || $request->input('routing_number') !== '*********'
            || substr($existing->account_number, -4) !== substr($request->input('account_number'), -4)
            ) {
            $rules += [
                'account_number' => 'required|numeric|confirmed',
                'routing_number' => 'required|numeric|digits:9|confirmed',
            ];
        }

        $data = $request->validate($rules);
        return new BankAccount($data);
    }

}