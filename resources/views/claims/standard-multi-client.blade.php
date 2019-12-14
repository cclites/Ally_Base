<?php
/**
 * @var \App\Claims\ClaimInvoice $claim The ClaimInvoice being printed
 * @var \App\Business $sender The related Business
 * @var \App\Billing\Payer $recipient The related Payer
 * @var \Illuminate\Support\Collection $itemGroups
 */
?>
@extends('claims.layouts.ally')

@section('header')
    @include('claims.partials.header-payer')
@endsection

@section('items')
    @include('claims.partials.items-table-payer')
@endsection

@section('footer')
    @include('claims.partials.footer')
@endsection