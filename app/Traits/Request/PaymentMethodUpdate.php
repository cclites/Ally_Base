<?php


namespace App\Traits\Request;


use App\BankAccount;
use App\Contracts\UserRole;
use App\CreditCard;
use App\Rules\CreditCardValid;
use Illuminate\Http\Request;

trait PaymentMethodUpdate
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\BankAccount|null $existing
     * @param \App\Contracts\UserRole $user
     * @return \App\BankAccount|bool|null
     */
    public function updateBankAccount(Request $request, UserRole $user, $existing = null)
    {
        $rules = [
            'nickname' => 'nullable',
            'account_type' => 'required|in:checking,savings',
            'account_holder_type' => 'required|in:business,personal',
            'name_on_account' => 'required',
        ];

        if (!$existing || strpos($request->input('account_number'), '****') !== 0) {
            $rules += [
                'account_number' => 'required|confirmed|numeric',
                'routing_number' => 'required|confirmed|numeric|digits:9',
            ];
        }

        $data = $request->validate($rules);

        if (!$existing) {
            $existing = new BankAccount($data);
        }
        else {
            $existing->fill($data);
        }

        if ($user->bankAccounts()->save($existing)) {
            return $existing;
        }
        return false;
    }

    public function updateCreditCard(Request $request, UserRole $user, $existing = null)
    {
        $rules = [
            'nickname' => 'nullable',
            'expiration_month' => 'required|digits_between:1,2',
            'expiration_year' => 'required|size:4',
            'name_on_card' => 'required',
        ];

        if (!$existing || strpos($request->input('number'), '****') !== 0) {
            $rules += [
                'number' => ['required', new CreditCardValid()],
                'cvv' => 'required|digits_between:3,4',
            ];
        }

        $data = $request->validate($rules);
        // Extract CVV
        $cvv = $data['cvv'];
        unset($data['cvv']);
        if ($cvv) {
            // TODO: Validate card with CVV
        }

        // Add Card Type
        if (isset($data['number'])) {
            $cardValidator = \Inacho\CreditCard::validCreditCard($data['number']);
            $data['type'] = $cardValidator['type'];
        }

        if (!$existing) {
            $existing = new CreditCard($data);
        }
        else {
            $existing->fill($data);
        }

        if ($user->creditCards()->save($existing)) {
            return $existing;
        }
        return false;
    }

}