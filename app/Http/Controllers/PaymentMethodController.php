<?php

namespace App\Http\Controllers;

use App\BankAccount;
use App\Client;
use App\CreditCard;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Rules\CreditCardValid;
use Illuminate\Http\Request;

class PaymentMethodController
{
    public function update(Request $request, Client $client, $type, $reference = 'The payment method')
    {
        if ($request->has('number')) {
            $class = CreditCard::class;
            $year = date('Y');
            $data = $request->validate([
                'nickname' => 'nullable',
                'number' => ['required', new CreditCardValid()],
                'cvv' => 'required|digits_between:3,4',
                'expiration_month' => 'required|digits_between:1,2',
                'expiration_year' => 'required|size:4',
                'name_on_card' => 'required',
            ]);

            // Extract CVV
            $cvv = $data['cvv'];
            unset($data['cvv']);
        }
        else if ($request->has('account_number')) {
            $class = BankAccount::class;
            $data = $request->validate([
                'nickname' => 'nullable',
                'account_number' => 'required|confirmed|numeric',
                'routing_number' => 'required|confirmed|numeric|digits:9',
                'account_type' => 'required|in:checking,savings',
                'account_holder_type' => 'required|in:business,personal',
                'name_on_account' => 'required',
            ]);
        }
        else {
            return new ErrorResponse(400, 'Invalid type');
        }

        \DB::beginTransaction();

        $model = new $class($data);
        $model->user_id = $client->id;
        $model->save();
        $saved = false;

        if ($type == 'primary') {
            $existing = $client->defaultPayment;
            $saved = $client->defaultPayment()->associate($model)->save();
        }
        else if ($type == 'backup') {
            $existing = $client->backupPayment;
            $saved = $client->backupPayment()->associate($model)->save();
        }

        if ($existing) {
            $existing->delete();
        }

        if ($saved) {
            \DB::commit();
            return new SuccessResponse($reference . ' has been saved.');
        }

        \DB::rollBack();
        return new ErrorResponse(500, $reference . ' could not be saved.');
    }
}
