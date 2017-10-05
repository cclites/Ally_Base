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

            if (strpos($request->input('number'), '****') === 0) {
                return $this->updateNonNumber($class, $request, $client, $type, $reference);
            }

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

            if (strpos($request->input('account_number'), '****') === 0) {
                return $this->updateNonNumber($class, $request, $client, $type, $reference);
            }

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

    /**
     * Update information, skipping account or card numbers
     *
     * @param $class
     * @param \Illuminate\Http\Request $request
     * @param \App\Client $client
     * @param $type
     * @param string $reference
     */
    public function updateNonNumber($class, Request $request, Client $client, $type, $reference = 'The payment method')
    {
        if ($type == 'primary') {
            $existing = $client->defaultPayment;
        }
        else if ($type == 'backup') {
            $existing = $client->backupPayment;
        }

        if (!$existing) {
            return new ErrorResponse(400, 'Cannot update a non-existent method.  Invalid number provided.');
        }

        if ($class == CreditCard::class) {
            $data = $request->validate([
                'nickname' => 'nullable',
                'expiration_month' => 'required|digits_between:1,2',
                'expiration_year' => 'required|size:4',
                'name_on_card' => 'required',
            ]);

            if (substr($request->input('number'), -4) != $existing->last_four) {
                return new ErrorResponse(400, 'Card number was changed but still contains asterisks.');
            }
        }

        if ($class == BankAccount::class) {
            $data = $request->validate([
                'nickname' => 'nullable',
                'account_type' => 'required|in:checking,savings',
                'account_holder_type' => 'required|in:business,personal',
                'name_on_account' => 'required',
            ]);

            if (substr($request->input('account_number'), -4) != $existing->last_four) {
                return new ErrorResponse(400, 'Account number was changed but still contains asterisks.');
            }
        }

        if ($existing->update($data)) {
            return new SuccessResponse($reference . ' has been saved.');
        }
        return new ErrorResponse(500, $reference . ' could not be saved.');
    }
}
