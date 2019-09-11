<?php
/**
 * @var \App\Claims\ClaimInvoice $claim
 */
?>
@extends('layouts.print')

@section('title', 'Claim Invoice #' . $claim->getName())

@push('head')
    @include('invoices.partials.styles')
@endpush

@section('content')
    <div class="">
        @include('claims.invoice-formats.partials.header')
        @yield('items')
        @yield('payments')
        @yield('notes')
        @include('claims.invoice-formats.partials.footer')
    </div>
@endsection