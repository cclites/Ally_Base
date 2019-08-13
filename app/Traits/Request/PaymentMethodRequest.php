<?php
namespace App\Traits\Request;

use Illuminate\Http\Request;

trait PaymentMethodRequest
{
    use BankAccountRequest;
    use CreditCardRequest;

    public function validatePaymentMethod(Request $request, $existing = null)
    {
        if ($request->has('number')) {
            return $this->validateCreditCard($request, $existing);
        }
        else if ($request->has('account_number')) {
            return $this->validateBankAccount($request, $existing);
        }

        return null;
    }
}