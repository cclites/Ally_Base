<?php
require __DIR__ . '/bootstrap.php';

////////////////////////////////////
//// Resync client rates with client caregivers
////////////////////////////////////

$clients = \App\Client::has('caregivers')->with(['rates', 'caregivers', 'defaultPayment'])->get();
$clients->each(function(\App\Client $client) {
    foreach($client->caregivers as $caregiver) {
        if ($client->rates->where('caregiver_id', $caregiver->id)->count()) {
            continue;
        }
        $paymentMethod = $client->getPaymentMethod() ?? new \App\Billing\Payments\Methods\CreditCard();
        \App\Billing\ClientRate::create([
            'client_id' => $client->id,
            'caregiver_id' => $caregiver->id,
            'client_hourly_rate' => multiply(add($caregiver->pivot->caregiver_hourly_rate, $caregiver->pivot->provider_hourly_fee), add(1, $paymentMethod->getAllyPercentage())),
            'caregiver_hourly_rate' => $caregiver->pivot->caregiver_hourly_rate ?? 0,
            'client_fixed_rate' => multiply(add($caregiver->pivot->caregiver_fixed_rate, $caregiver->pivot->provider_fixed_fee), add(1, $paymentMethod->getAllyPercentage())),
            'caregiver_fixed_rate' => $caregiver->pivot->caregiver_fixed_rate ?? 0,
            'effective_start' => '2019-01-01',
            'effective_end' => '9999-12-31',
        ]);
    }
});