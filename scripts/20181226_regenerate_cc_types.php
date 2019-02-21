<?php

require __DIR__ . '/bootstrap.php';

DB::beginTransaction();
$cards = \App\Billing\Payments\Methods\CreditCard::all()->each(function(\App\Billing\Payments\Methods\CreditCard $card) {
   $card->type = \App\Billing\Payments\Methods\CreditCard::getType($card->number);
   $card->save();
});
DB::commit();