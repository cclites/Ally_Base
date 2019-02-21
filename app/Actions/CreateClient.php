<?php
namespace App\Actions;

use App\Billing\Contracts\ChargeableInterface;
use App\Client;
use App\Events\ClientCreated;
use App\OnboardStatusHistory;

class CreateClient
{
    const AUTO_EMAIL = "tmp";

    public function create(array $data, ?ChargeableInterface $paymentMethod = null): ?Client
    {
        if (empty($data['email'])) {
            $data['email'] = self::AUTO_EMAIL;
        }

        if ($client = Client::create($data)) {
            if ($client->email === self::AUTO_EMAIL) {
                $client->setAutoEmail()->save();
            }

            if ($paymentMethod) {
                $client->setPaymentMethod($paymentMethod);
            }

            $history = new OnboardStatusHistory([
                'status' => $data['onboard_status'],
            ]);
            $client->onboardStatusHistory()->save($history);

            event(new ClientCreated($client));

            return $client;
        }

        return null;
    }
}