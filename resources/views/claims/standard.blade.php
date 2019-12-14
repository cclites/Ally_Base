<?php
/**
 * @var \App\Claims\ClaimInvoice $claim The ClaimInvoice being printed
 * @var \App\Business $sender The related Business
 * @var \App\Billing\Payer $recipient The related Payer
 * @var \Illuminate\Support\Collection $itemGroups
 */
?>
@extends('claims.invoice-formats.ally')

@section('header')
    @include('claims.invoice-formats.partials.header-regular')
@endsection

@section('items')
    @include('claims.invoice-formats.partials.items-table-client')
@endsection

@section('footer')
    @include('claims.invoice-formats.partials.footer')
@endsection