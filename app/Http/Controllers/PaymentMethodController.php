<?php

namespace App\Http\Controllers;

use App\BankAccount;
use App\Client;
use App\CreditCard;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Traits\Request\PaymentMethodUpdate;
use Illuminate\Http\Request;

class PaymentMethodController
{
    use PaymentMethodUpdate;

    public function update(Request $request, Client $client, $type, $reference = 'The payment method', $redirect = null)
    {
        if ($type == 'primary') {
            $existing = $client->defaultPayment;
        }
        else if ($type == 'backup') {
            $existing = $client->backupPayment;
        }
        else {
            return new ErrorResponse(400, 'Invalid request');
        }

        if ($request->has('number')) {
            if (!$existing instanceof CreditCard) {
                $existing = null;
            }
            $method = $this->updateCreditCard($request, $client, $existing);
        }
        else if ($request->has('account_number')) {
            if (!$existing instanceof BankAccount) {
                $existing = null;
            }
            $method = $this->updateBankAccount($request, $client, $existing);
        }
        else {
            return new ErrorResponse(400, 'Invalid request');
        }

        if ($method) {
            if ($type == 'primary') {
                $client->defaultPayment()->associate($method)->save();
            }
            else {
                $client->backupPayment()->associate($method)->save();
            }
            return new SuccessResponse($reference . ' has been updated.', [], $redirect);
        }

        return new ErrorResponse(500, $reference . ' could not be saved.');
    }
}
