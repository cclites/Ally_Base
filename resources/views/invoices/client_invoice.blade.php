<?php
/**
 * @var \App\Billing\ClientInvoice $invoice
 * @var \App\Contracts\ContactableInterface $sender
 * @var \App\Contracts\ContactableInterface $recipient
 * @var \Illuminate\Support\Collection $itemGroups
 * @var \Illuminate\Support\Collection|App\Billing\Payment[] $payments
 */
?>
@extends('invoices.layout')

@section('items')
    @include('invoices.partials.items_table')
@endsection

@section('payments')
    @include('invoices.partials.client_payments_table')
@endsection

@section('notes')
    @include('invoices.partials.payer_notes')
@endsection