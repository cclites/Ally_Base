<?php

require __DIR__ . '/bootstrap.php';



function addNotesToItem(\App\Billing\BaseInvoiceItem $item)
{
    if ($item->getInvoiceable() && $notes = $item->getInvoiceable()->getItemNotes()) {
        $item->update(['notes' => $notes]);
    }
}


DB::beginTransaction();

\App\Billing\ClientInvoiceItem::chunk(1000, function($items) {
    $items->each(function(\App\Billing\ClientInvoiceItem $item) {
        addNotesToItem($item);
    });
});

\App\Billing\CaregiverInvoiceItem::chunk(1000, function($items) {
    $items->each(function(\App\Billing\CaregiverInvoiceItem $item) {
        addNotesToItem($item);
    });
});

\App\Billing\BusinessInvoiceItem::chunk(1000, function($items) {
    $items->each(function(\App\Billing\BusinessInvoiceItem $item) {
        addNotesToItem($item);
    });
});

DB::commit();