<?php

require __DIR__ . '/bootstrap.php';

DB::beginTransaction();
$cards = \App\CreditCard::all()->each(function(\App\CreditCard $card) {
   $card->type = \App\CreditCard::getType($card->number);
   $card->save();
});
DB::commit();