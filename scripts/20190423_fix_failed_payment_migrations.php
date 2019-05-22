<?php
require __DIR__ . '/bootstrap.php';


////////////////////////////////////
//// Remove duplicate caregiver invoices migrated from failed deposits during the March 1st release.
////////////////////////////////////

use App\Billing\CaregiverInvoice;
use App\Billing\CaregiverInvoiceItem;

$records = DB::select("SELECT i.id FROM caregiver_invoices i
JOIN invoice_deposits id ON id.invoice_id = i.id AND id.invoice_type = 'caregiver_invoices'
JOIN deposits d ON d.id = id.deposit_id
WHERE id.amount_applied > 0
AND d.success = 0
AND (SELECT count(*) FROM invoice_deposits id2 WHERE id2.invoice_id = i.id AND id.invoice_type = 'caregiver_invoices') = 1");


$invoices = CaregiverInvoice::with('items')->whereIn('id', array_map(function($row) { return $row->id; }, $records))->get();
echo "Starting count: " . $invoices->count() . "\n";

$invoices = $invoices->filter(function(CaregiverInvoice $invoice) {
     foreach($invoice->items as $item) {
         if ($item->invoiceable_type === "shifts") {
             $query = CaregiverInvoiceItem::where('invoice_id', '!=', $invoice->id)
                 ->where('invoiceable_type', $item->invoiceable_type)
                 ->where('invoiceable_id', $item->invoiceable_id);
             if (!$query->exists()) {
                 return false;
             }
         }
     }
     return true;
});

echo "Ending count: " . $invoices->count() . "\n";

DB::beginTransaction();

/** @var CaregiverInvoice $invoice */
foreach($invoices as $invoice) {
    $invoice->deposits()->sync([]);
    $invoice->delete();
}

DB::commit();