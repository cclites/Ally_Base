@extends('layouts.print')

@section('title', 'Payment Details - Print')

@section('content')
    <style>
        .header-left,
        .footer-left {
            float: left;
            width: 75%;
            padding-left: 0;
        }

        .header-right,
        .footer-right {
            float: left;
            width: 25%;
            padding-right: 0;
        }

        .header-right table tr td {
            padding-left: .5rem;
        }

        .print-header {
            margin: 1rem 0;
        }
    </style>
    <div class="container-fluid">
        <div class="row print-header">
            <div class="header-left">
                {{--<img src="{{ asset('images/') }}" alt="">--}}
                <div class="h4">{{ $payment->business->name }}</div>
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
                <div class="h4">Statement</div>
                <div>{{ $payment->client->name }}</div>
                <table style="float: right;">
                    <tr>
                        <td>Payment Date</td>
                        <td>{{ $payment->created_at->format('m/d/Y') }}</td>
                    </tr>
                    <tr>
                        <td>Care Week</td>
                        <td>
                            @if(!is_null($payment->week))
                                {{ $payment->week->start->format('m/d') }} - {{ $payment->week->end->format('m/d') }}
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-bordered">
                    <tbody>
                    <tr>
                        <th colspan="4"></th>
                        <th colspan="4">Rates</th>
                        <th></th>
                    </tr>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Activities Performed</th>
                        <th>Caregiver</th>
                        <th>Rate</th>
                        <th>Hours Type</th>
                        <th>Mileage</th>
                        <th>Hours</th>
                        <th>Total</th>
                    </tr>
                    @foreach($shifts as $shift)
                        <tr >
                            <td>
                                {{ $shift->checked_in_time->format('m/d/Y') }}
                            </td>
                            <td>
                                {{ $shift->checked_in_time->format('g:i a') }} - {{ $shift->checked_out_time->format('g:i a') }}
                            </td>
                            <td>
                                @foreach($shift->activities as $activity)
                                    <div>{{ $activity }}</div>
                                @endforeach
                            </td>
                            <td>
                                {{ $shift->caregiver_name }}
                            </td>
                            <td>
                                ${{ $shift->hourly_total }}
                            </td>
                            <td>
                                {{ $shift->hours_type }}
                            </td>
                            <td>
                                {{ $shift->mileage }}
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
                <p>This is a statement. Your payment was processed on {{ $payment->created_at->format('m/d/Y') }} using your payment information on file.</p>
            </div>
            <div class="footer-right">
                <table class="table">
                    <tbody>
                    <tr>
                        <td>Total</td>
                        <td>
                            &dollar;{{ number_format($shifts->sum('shift_total'), 2) }}
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
                    Ally Contact Info
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="pull-right">&copy; {{ \Carbon\Carbon::now()->year }} Ally</div>
            </div>
        </div>
    </div>
@endsection