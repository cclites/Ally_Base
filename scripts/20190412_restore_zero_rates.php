<?php
require __DIR__ . '/bootstrap.php';

$rateStore = unserialize(file_get_contents(storage_path('saved_rates_for_business_37.txt')));

DB::beginTransaction();

foreach($rateStore as $shiftId => $rateArray) {
    $shift = App\Shift::findOrFail($shiftId);
    $shift->update([
        'client_rate' => $rateArray['client_rate'],
        'caregiver_rate' => $rateArray['caregiver_rate'],
    ]);
}

DB::commit();

