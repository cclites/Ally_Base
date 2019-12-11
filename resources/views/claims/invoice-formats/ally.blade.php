<?php
/**
 * @var \App\Claims\ClaimInvoice $claim The ClaimInvoice being printed
 */
?>
@extends('layouts.print')

@section('title', 'Claim Invoice #' . $claim->getName())

@push('head')
    @include('invoices.partials.styles')
@endpush

@section('content')
    <div class="">
        @if ($claim->getType() == \App\Claims\ClaimInvoiceType::PAYER())
            @include('claims.invoice-formats.partials.header-payer')
        @else
            <!-- All the same Client -->
            @if ($claim->payer_id == \App\Billing\Payer::PRIVATE_PAY_ID)
                @include('claims.invoice-formats.partials.header-private-pay')
            @else
                @include('claims.invoice-formats.partials.header-regular')
            @endif
        @endif

        @yield('items')

        @yield('payments')

        @yield('notes')

        @include('claims.invoice-formats.partials.footer')
    </div>
@endsection