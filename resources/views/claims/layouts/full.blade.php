<?php
/**
 * @var \App\Claims\ClaimInvoice $claim The ClaimInvoice being printed
 */
?>
@extends('layouts.print')

@section('title', 'C-Invoice #' . $claim->getName())

@push('head')
    @include('invoices.partials.styles')
    <style>
        .col-sm-6 {
            float: left;
            width: 50%;
        }
        .mb-1 { margin-bottom: 0.25rem !important; }
        .mb-2 { margin-bottom: 0.50rem !important; }
        .mb-3 { margin-bottom: 1rem !important; }
        .mb-4 { margin-bottom: 1.50rem !important; }
        .mb-5 { margin-bottom: 3rem !important; }
        .signature svg {
            max-height: 200px;
            width: 100%;
        }
        body { font-size: 16px!important; }
        h4 { color: #000; font-weight: 600 }
        table td { text-align: left!important; }
        table th { text-align: left!important; }
    </style>
@endpush

@section('content')
    @yield('cover-page')

    @yield('header')
    @yield('items')
    @yield('notes')
    @yield('footer')

    @yield('services')
@endsection
