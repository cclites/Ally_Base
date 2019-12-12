<?php
/**
 * @var \App\Claims\ClaimInvoice $claim The ClaimInvoice being printed
 * @var \App\Business $sender The related Business
 * @var \App\Billing\Payer $recipient The related Payer
 * @var \Illuminate\Support\Collection $itemGroups
 */
?>
@extends('claims.invoice-formats.ally')

@if ($claim->getType() == \App\Claims\ClaimInvoiceType::PAYER())
    @include('claims.invoice-formats.partials.items-table-payer')
@else
    @include('claims.invoice-formats.partials.items-table-client')
@endif
