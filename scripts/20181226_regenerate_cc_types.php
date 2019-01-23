<?php

require __DIR__ . '/bootstrap.php';

DB::beginTransaction();
$cards = \App\Billing\PaymentMethods\CreditCard::all()->each(function(\App\Billing\PaymentMethods\CreditCard $card) {
   $card->type = \App\Billing\PaymentMethods\CreditCard::getType($card->number);
   $card->save();
});
DB::commit();