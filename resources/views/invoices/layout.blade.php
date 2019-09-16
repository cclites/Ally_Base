<?php
/**
 * @var \App\Billing\Contracts\InvoiceInterface $invoice
 * @var \App\Contracts\ContactableInterface $sender
 * @var \App\Contracts\ContactableInterface $recipient
 * @var \Illuminate\Support\Collection $itemGroups
 * @var \Illuminate\Support\Collection $payments
 */
?>
@extends('layouts.print')

@section('title', 'Invoice #' . $invoice->getName())

@push('head')
    @include('invoices.partials.styles')
@endpush

@section('content')
    <div class="">
        @include('invoices.partials.header')
        @yield('items')
        @yield('payments')
        @yield('notes')
        @include('invoices.partials.footer')
    </div>
@endsection