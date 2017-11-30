<?php


namespace App\Traits\Request;


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
            || strpos($request->input('account_number'), '****') !== 0
            || substr($existing->account_number, -4) !== substr($request->input('account_number'), -4)
            ) {
            $rules += [
                'account_number' => 'required|numeric|confirmed',
                'routing_number' => 'required|numeric|digits:9|confirmed',
            ];
        }

        return $request->validate($rules);
    }

}