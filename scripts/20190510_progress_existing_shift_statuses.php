<?php
require __DIR__ . '/bootstrap.php';

use App\Billing\Invoiceable\ShiftService;
use App\Billing\Queries\InvoiceableQuery;
use App\Shift;

\DB::beginTransaction();
$query = (new InvoiceableQuery(new Shift()))->notBelongingToAnOldFinalizedShift();
$shifts = (clone $query)->hasClientInvoice()->get();
/** @var Shift $shift */
foreach($shifts as $shift) {
    $shift->statusManager()->ackClientInvoice();
}
echo $shifts->count() . " acknowledged client invoice.\n";

$shifts = (clone $query)->hasClientInvoicesPaid()->doesntHaveCaregiverInvoice()->get();
/** @var Shift $shift */
foreach($shifts as $shift) {
    $shift->statusManager()->ackPayment();

}
echo $shifts->count() . " acknowledged client payment.\n";


$shifts = (clone $query)->hasClientInvoicesPaid()->hasCaregiverInvoicesPaid()->hasBusinessInvoicesPaid()->get();
/** @var Shift $shift */
foreach($shifts as $shift) {
    $shift->statusManager()->update(Shift::PAID);
}
echo $shifts->count() . " updated to PAID.\n";

$query = new InvoiceableQuery(new ShiftService());
$shiftServices = (clone $query)->hasClientInvoicesPaid()->hasCaregiverInvoicesPaid()->hasBusinessInvoicesPaid()->get();
/** @var ShiftService $shiftService */
foreach($shiftServices as $shiftService) {
    $shiftService->getShift()->statusManager()->update(Shift::PAID);
}
echo $shiftServices->count() . " related services updated to PAID.\n";

\DB::commit();
