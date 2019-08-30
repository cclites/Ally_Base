<?php
namespace App\Http\Controllers\Business;

use App\Billing\Payments\Methods\Trust;
use App\Client;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Traits\Request\PaymentMethodRequest;
use Illuminate\Http\Request;

class ClientPaymentMethodController extends BaseController
{
    use PaymentMethodRequest;

    public function store(Request $request, Client $client, string $type)
    {
        $this->authorize('update', $client);

        $backup = ($type === 'backup');

        if ($request->input('use_business')) {
            if (!$client->business->paymentAccount) return new ErrorResponse(400, 'There is no provider payment account on file.');
            if ($client->setPaymentMethod($client->business, $backup)) {
                return $this->paymentMethodResponse($client, 'The payment method has been set to the provider payment account.');
            }
            return new ErrorResponse(500, 'The payment method could not be updated.');
        }

        if ($request->input('use_trust')) {
            if (is_admin()) {
                $trust = Trust::firstOrCreate($client);
                $client->setPaymentMethod($trust, $backup);
                return $this->paymentMethodResponse($client, 'The payment method has been set to a trust account.');
            }
            return new ErrorResponse(403, 'You cannot modify the payment method of a Trust Account.');
        }


        if ($method = $this->validatePaymentMethod($request, $client->getPaymentMethod($backup)))
        {
            if ($client->setPaymentMethod($method, $backup)) {
                return $this->paymentMethodResponse($client, 'The payment method has been updated.');
            }
        }
        return new ErrorResponse(500, 'The payment method could not be updated.');
    }

    public function delete(Client $client, string $type)
    {
        $this->authorize('update', $client);

        $relation = $type == 'backup' ? $client->backupPayment() : $client->defaultPayment();

        if ($relation->first() instanceof Trust) {
            if (!is_admin()) {
                return new ErrorResponse(403, 'You cannot modify the payment method of a Trust Account.');
            }
        }

        $relation->dissociate();
        $client->save();

        return $this->paymentMethodResponse($client, 'The payment method has been removed.');
    }

    protected function paymentMethodResponse(Client $client, $message)
    {
        $allyRate = $client->getAllyPercentage();
        $paymentTypeMessage = "Active Payment Type: " . $client->getPaymentType() . " (" . round($allyRate * 100, 2) . "% Processing Fee)";
        $data['payment_text'] = $paymentTypeMessage;
        $data['ally_rate'] = $allyRate;
        return new SuccessResponse($message, $data, '.');
    }

}