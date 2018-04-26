@extends('layouts.print')

@section('title', 'Payment Details - Print')

@section('content')
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

        .print-header {
            margin: 1rem 0;
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
    </style>
    <div class="container-fluid">
        <div class="row print-header">
            <div class="header-left">
                {{--<img src="{{ asset('images/') }}" alt="">--}}
                <div class="h3">{{ $payment->business->name }}</div>
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
                    @if($payment->client->user->addresses()->count())
                        <tr>
                            <td colspan="2">
                                <div>{{ $payment->client->user->addresses->first()->address1 }}</div>
                                @if($payment->client->user->addresses->first()->address2)
                                    <div>{{ $payment->client->user->addresses->first()->address2 }}</div>
                                @endif
                                <div>
                                    {{ $payment->client->user->addresses->first()->city }},
                                    {{ $payment->client->user->addresses->first()->state }}
                                    {{ $payment->client->user->addresses->first()->zip }}
                                </div>
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
        <div class="row">
            <div class="col-md-12">
                <table class="table table-bordered shifts-table">
                    <tbody>
                    <tr class="bg-info">
                        <th>Date</th>
                        <th>Time</th>
                        <th>EVV</th>
                        <th width="35%">Activities Performed</th>
                        <th>Caregiver</th>
                        <th>Rate</th>
                        <th>Hours</th>
                        <th>Total</th>
                    </tr>
                    @if($payment->adjustment)
                        <tr>
                            <td>{{ $payment->created_at->setTimezone($timezone)->format('m/d/Y') }}</td>
                            <td>Manual Adjustment</td>
                            <td colspan="3">{{ $payment->notes }}</td>
                            <td>${{ $payment->amount }}</td>
                        </tr>

                    @endif
                    @foreach($shifts as $shift)
                        <tr >
                            <td>
                                {{ $shift->checked_in_time->setTimezone($timezone)->format('m/d/Y') }}
                            </td>
                            <td>
                                {{ $shift->checked_in_time->setTimezone($timezone)->format('g:ia') }} -
                                {{ $shift->checked_out_time->setTimezone($timezone)->format('g:ia') }}
                            </td>
                            <td>{{ $shift->EVV ? 'Yes' : 'No' }}</td>
                            <td>
                                <div>{{ $shift->activities->implode(', ') }}</div>
                                {{--@foreach($shift->activities as $activity)--}}
                                    {{--<div>{{ $activity }}</div>--}}
                                {{--@endforeach--}}
                            </td>
                            <td>
                                {{ $shift->caregiver_name }}
                            </td>
                            <td>
                                ${{ $shift->hourly_total }}
                            </td>
                            <td>
                                {{ $shift->hours }}
                            </td>
                            <td>
                                &dollar;{{ $shift->shift_total }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
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