@extends('layouts.print')

@section('title', 'Payment Details - Print')

@push('head')
    <style>
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
            background-color: #1e88e5!important;
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
            background-color: #ccc;
            padding: 15px;
        }

        .logo img {
            max-height: 80px;
        }
    </style>
@endpush

@section('content')
    <div class="">
        <div class="row print-header">
            <div class="header-left">
                @include('layouts.partials.print_logo')
                <div class="h4">Associated Provider: {{ $payment->business->name }}</div>
                <br>
                <div>{{ $payment->business->address1 }}</div>
                @if($payment->business->address2)
                    <div>{{ $payment->business->address2 }}</div>
                @endif
                @if($payment->business->city && $payment->business->state)
                    <span>{{ $payment->business->city }}</span>,
                    <span>{{ $payment->business->state }}</span>
                @elseif($payment->business->city)
                    {{ $payment->busienss->city }}
                @elseif($payment->business->state)
                    {{ $payment->business->state }}
                @endif
                <span>{{ $payment->business->zip }}</span>
                <div>{{ $payment->business->phone1 }}</div>
            </div>
            <div class="text-right header-right">
                <div class="h3">Statement</div>
                <br>
                <table class="header-right-table">
                    <tr>
                        <td>Payment Date</td>
                        <td>{{ $payment->created_at->setTimezone($timezone)->format('m/d/Y') }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>{{ $payment->client->name }}</strong>
                        </td>
                    </tr>
                    @if($address = $payment->client->addresses->where('type', 'evv')->first() ?? $payment->client->addresses->first())
                        <tr>
                            <td colspan="2">
                                {{ $address->address1 }}<br />
                                @if($address->address2)
                                    {{ $address->address2 }}<br />
                                @endif
                                {{ $address->city }}, {{ $address->state }}{{ $address->zip }}
                                @if($payment->client->user->phoneNumbers->count())
                                    <div>{{ $payment->client->user->phoneNumbers->first()->number }}</div>
                                @endif
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td><strong>Payment Status: </strong></td>
                        <td>
                            @if ($payment->success)
                                <span style="color: green">Completed</span>
                            @else
                                <span style="color: darkred">Failed</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @include('clients.print.statement_shifts_table', ['report_type' => 'full'])

        <div class="row">
            <div class="footer-left">
                <p>This is a statement. Your payment was processed on {{ $payment->created_at->setTimezone($timezone)->format('m/d/Y') }} using your payment information on file.</p>
            </div>
            <div class="footer-right">
                <table class="table">
                    <tbody>
                    <tr>
                        <td><strong>Total</strong></td>
                        <td>
                            &dollar;{{ $payment->amount }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <em>Nothing due. This is a payment statement only.</em>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <p class="text-center">
                    (800) 930-0587<br>
                    allyms.com<br>
                    support@allyms.com
                </p>
                <p class="text-center"><em>Thank you for your business!</em></p>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="pull-right" style="margin-right: 1rem;">&copy; {{ \Carbon\Carbon::now()->year }} Ally</div>
            </div>
        </div>
    </div>
@endsection