@extends('layouts.print')

@section('title', "Claim $claimNumber")

@push('head')
    <style>
        .col-sm-6 {
            float: left;
            width: 50%;
        }

        #cover-page {
            display:flex;
            position:relative;
            top:0;
            bottom:0;
            right:0;
            left:0;
        }
    </style>
@endpush

@section('content')
    <div class="page" id="cover-page">
        <div style="margin: auto">
            <p>Attention: Claims</p>
            <p>Policyholder: {{ $client->ltci_name ?: $client->name }}</p>
            <p>Policy #{{ $policyNumber }}</p>
        </div>
    </div>

    <div class="page" id="summary">
        <div class="row print-header">
            <div class="header-left">
                <div class="logo"><img src="{{ asset('/images/AllyLogo.png') }}" /></div>
                <div class="h4">Associated Provider: {{ $business->name }}</div>
                <br>
                <div>{{ $business->address1 }}</div>
                @if($business->address2)
                    <div>{{ $business->address2 }}</div>
                @endif
                @if($business->city && $business->state)
                    <span>{{ $business->city }}</span>,
                    <span>{{ $business->state }}</span>
                @elseif($business->city)
                    {{ $payment->busienss->city }}
                @elseif($business->state)
                    {{ $business->state }}
                @endif
                <span>{{ $business->zip }}</span>
                <div>{{ $business->phone1 }}</div>
            </div>
            <div class="text-right header-right">
                <div class="h3">Claim</div>
                <br>
                <table class="header-right-table">
                    <tr>
                        <td>Policy #</td>
                        <td>{{ $policyNumber }}</td>
                    </tr>
                    <tr>
                        <td>Claim #</td>
                        <td>{{ $claimNumber }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>{{ $client->ltci_name ?: $client->name }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            {{ $client->ltci_address }}<br />
                            {{ $client->ltci_city }}, {{ $client->ltci_state }} {{ $client->ltci_zip }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @include('clients.print.statement_shifts_table')

        <div class="row">
            <div class="footer-left"></div>
            <div class="footer-right">
                <strong>Total:</strong> &dollar;{{ $totalAmount }}
            </div>
        </div>
    </div>

    @foreach($shifts as $shift)
        <div class="page">
            @include('business.shifts.print_details')
        </div>
    @endforeach
@endsection