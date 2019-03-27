<?php
namespace App\Actions;

use App\Billing\Contracts\ChargeableInterface;
use App\Client;
use App\Events\ClientCreated;
use App\OnboardStatusHistory;
use App\ClientAgreementStatusHistory;

class CreateClient
{
    const AUTO_EMAIL = "tmp";

    public function create(array $data, ?ChargeableInterface $paymentMethod = null): ?Client
    {
        if (empty($data['email'])) {
            $data['email'] = self::AUTO_EMAIL;
        }

        if (empty($data['username'])) {
            $data['username'] = Client::getAutoUsername();
        }

        if ($client = Client::create($data)) {
            if ($client->email === self::AUTO_EMAIL) {
                $client->setAutoEmail()->save();
            }

            if ($paymentMethod) {
                $client->setPaymentMethod($paymentMethod);
            }

            if (isset($data['agreement_status'])) {
                $history = new ClientAgreementStatusHistory([
                    'status' => $data['agreement_status'],
                ]);
                $client->agreementStatusHistory()->save($history);
            }

            event(new ClientCreated($client));

            return $client;
        }

        return null;
    }
}