<?php

require __DIR__ . '/bootstrap.php';

////////////////////////////////////
//// Clear out old $0 provider fee shifts (add them to a new invoice that should just be $0)
////////////////////////////////////

foreach(\App\Business::all() as $business) {
    $query = new \App\Billing\Queries\InvoiceableQuery(new \App\Shift());
    $shifts = $query->forBusinesses([$business->id])
        ->hasClientInvoicesPaid()
        ->doesntHaveBusinessInvoice()
        ->whereIn('status', ['PAID', 'PAID_CAREGIVER_ONLY'])
        ->where('provider_fee', '0')
        ->get();

    if ($shifts->count()) {
        $invoice = \App\Billing\BusinessInvoice::create([
            'name' => \App\Billing\BusinessInvoice::getNextName($business->id),
            'business_id' => $business->id,
            'created_at' => '2019-03-06 00:00:00',
        ]);

        /** @var \App\Shift $shift */
        foreach($shifts as $shift) {
            $item = new \App\Billing\BusinessInvoiceItem([
                'group' => $shift->getItemGroup(\App\Billing\BusinessInvoice::class),
                'name' => $shift->getItemName(\App\Billing\BusinessInvoice::class),
                'units' => $shift->duration(),
                'client_rate' => $clientRate = $shift->costs()->getTotalHourlyCost(),
                'caregiver_rate' => $shift->caregiver_rate,
                'ally_rate' => subtract($clientRate, add($shift->caregiver_rate, $shift->provider_fee)),
                'rate' => 0,
                'total' => 0,
                'date' => $shift->getItemDate(),
            ]);
            $item->associateInvoiceable($shift);
            $invoice->addItem($item);
        }
    }

}