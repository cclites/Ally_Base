<?php
namespace App\Traits\Request;

use App\CreditCard;
use App\Rules\CreditCardValid;
use Illuminate\Http\Request;

trait CreditCardRequest
{
    public function validateCreditCard(Request $request, $existing = null)
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
        if (isset($data['cvv'])) {
            $cvv = $data['cvv'];
            unset($data['cvv']);
        }

        $card = new CreditCard($data);
        if (isset($cvv)) {
            // To-do: Validate credit card with CVV
        }

        return $card;
    }
}