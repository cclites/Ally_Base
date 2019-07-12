<?php
/**
 * @var \App\Billing\ClientInvoice $invoice
 * @var \App\Contracts\ContactableInterface $sender
 * @var \App\Contracts\ContactableInterface $recipient
 * @var \Illuminate\Support\Collection $itemGroups
 * @var \Illuminate\Support\Collection|App\Billing\Payment[] $payments
 */
?>
<div class="" style="page-break-after: always;">
    @include('invoices.partials.header')
    @include('invoices.partials.items_table')
    @include('invoices.partials.client_payments_table')
    @include('invoices.partials.footer')
</div>
