<?php
/**
 * @var \App\Claims\ClaimInvoice $claim The ClaimInvoice being printed
 * @var \App\Business $sender The related Business
 * @var \App\Billing\Payer $recipient The related Payer
 * @var \App\Client|null $client The client model related to the claim
 */
?>
@extends('claims.layouts.full')

@section('cover-page')
    <div class="page">
        <div class="text-center" style="margin: 250px 0;">
            <h4>Attention: Claims</h4>
            <h4>Policyholder: {{ $client->name }}</h4>
            @foreach($clientData as $data)
                <h4>{{ $data }}</h4>
            @endforeach
        </div>
    </div>
@endsection

<div class="page">
    @section('header')
        @include('claims.partials.header-regular')
    @endsection

    @section('items')
        @include('claims.partials.items-table-client')
    @endsection

    @section('footer')
        @include('claims.partials.footer')
    @endsection
</div>

@section('services')
    @foreach($claim->serviceItems as $item)
        <div class="page">
            @include('claims.partials.service-details', ['item' => $item, 'service' => $item->claimable])
        </div>
    @endforeach
@endsection