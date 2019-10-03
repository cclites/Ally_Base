@extends('layouts.print')

@section('title', "Claim $claimNumber")

@push('head')
    <style>
        .col-sm-6 {
            float: left;
            width: 50%;
        }

         body {
             color: #000;
         }

        .header-left,
        .footer-left {
            float: left;
            width: 65%;
            padding-left: 0;
        }

        .header-right,
        .footer-right {
            float: left;
            width: 35%;
            padding-right: 0;
        }

        .footer-left {
            padding-left: 2rem;
            padding-right: 4rem;
        }

        .header-right table tr td {
            padding-left: .5rem;
        }

        .shifts-table {
            margin-top: 2rem;
            font-size: 1.4rem;
        }

        .bg-info {
            color: white;
        }

        .header-right-table {
            float: right;
        }

        .header-right-table td,
        .header-right-table th {
            text-align: left;
            padding: .5rem .75rem;
        }

        .print-header {
            margin: 0;
            background-color: transparent;
        }

        .logo img {
            max-height: 80px;
        }
    </style>
@endpush

@section('content')
        <div class="page" id="cover-page">
            <div class="row col text-center" style="margin: 250px 0;">
                <h4>Attention: Claims</h4>
                <h4>Policyholder: {{ $client->name }}</h4>
                <h4>Policy #{{ $policyNumber }}</h4>
            </div>
        </div>

        <div class="page" id="summary">
            <div class="row print-header">
                <div class="header-left">
                    @include('layouts.partials.print_header')
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
                    <div class="h3">Statement</div>
                    <br>
                    <table class="header-right-table">
                        <tr>
                            <td>Policy Number:</td>
                            <td>{{ $policyNumber }}</td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <strong>{{ $client->name }}</strong>
                            </td>
                        </tr>
                        @if($address = $client->addresses->where('type', 'evv')->first() ?? $client->addresses->first())
                            <tr>
                                <td colspan="2">
                                    {{ $address->address1 }}<br />
                                    @if($address->address2)
                                        {{ $address->address2 }}<br />
                                    @endif
                                    {{ $address->city }}, {{ $address->state }} {{ $address->zip }}
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            @include('clients.print.statement_shifts_table')

            @if ($report_type != 'notes')
            <div class="row">
                <div class="footer-left">
                    <p></p>
                </div>
                <div class="footer-right">
                    <table class="table">
                        <tbody>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td>
                                &dollar;{{ $totalAmount }}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
            
            {{--<div class="row col-lg-12">--}}
                {{--<div class="pull-right">--}}
                    {{--<strong>Total:</strong> &dollar;{{ $totalAmount }}--}}
                {{--</div>--}}
            {{--</div>--}}
        </div>

        @foreach($shifts as $shift)
            <div class="page">
                @include('business.shifts.print_details')
            </div>
        @endforeach
@endsection