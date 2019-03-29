<?php


namespace App\Traits\Request;


use App\Billing\Payments\Methods\BankAccount;
use App\Responses\ValidationErrorResponse;
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
            $bankAccountChange = true;
            $rules += [
                'account_number' => 'required|numeric|confirmed',
                'routing_number' => 'required|numeric|digits:9|confirmed',
            ];
        }

        $data = $request->validate($rules);

        if (isset($bankAccountChange) && ! $request->input('ignore_validation')) {
            // Validate the bank account with Microbilt
            $mb = new Microbilt(config('services.microbilt.id'), config('services.microbilt.password'));
            $result = $mb->verifyBankAccount($request->name_on_account, $request->account_number, $request->routing_number);
            if (isset($result['valid']) && $result['valid'] === false) {
                (new ValidationErrorResponse('account_number', 'The routing number and account number you entered did not pass our verification check.'))
                   ->toResponse($request)
                   ->send();
            }
        }

        return new BankAccount($data);
    }

}
