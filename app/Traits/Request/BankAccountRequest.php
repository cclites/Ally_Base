<?php


namespace App\Traits\Request;


use App\BankAccount;
use App\Services\Microbilt;
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

        // Validate the bank account with Microbilt
        $mb = new Microbilt(config('services.microbilt.id'), config('services.microbilt.password'));
        $result = $mb->verifyBankAccount(request()->name, request()->account_no, request()->routing_no);
        if (!$result['valid']) {
            // Throw a misc validation error (account number as email) with a relevant message
            $request->validate(
                ['account_number' => 'required|email'],
                ['account_number.*' => 'The routing number and account number you entered were determined to be invalid.']
            );
        }

        return new BankAccount($data);
    }

}