<?php
/**
 * @var \App\Claims\ClaimInvoice $claim The ClaimInvoice being printed
 */
?>
@extends('layouts.print')

@section('title', 'C-Invoice #' . $claim->getName())

@push('head')
    @include('invoices.partials.styles')
@endpush

@section('content')
    @yield('header')
    @yield('items')
    @yield('notes')
    @yield('footer')
@endsection
