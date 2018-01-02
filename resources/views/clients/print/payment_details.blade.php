@extends('layouts.print')

@section('title', 'Payment Details - Print')

@section('content')
    <div class="container-fluid">
        <div class="row" style="padding: 10px 0;">
            <div class="col-xs-4">
                <div>{{ with($c = $payment->client)->name }}</div>
                @if ($a = $c->evvAddress)
                    <div>{{ $a->address1 }}</div>
                    <div>{{ $a->address2 }}</div>
                    <span>{{ $a->city }}</span>,
                    <span>{{ $a->state }}</span>
                    <span>{{ $a->zip }}</span>
                    {{--<div> WHAT ABOUT PHONE ?? </div>--}}
                @endif
            </div>
            <div class="col-xs-4 text-center">
                <h2 style="margin-top: 0px;">Statement</h2>
            </div>
            {{--
                <div class="col">
                    <div>Care Services Statement For:</div>
                    <div>{{ $payment->client->name }}</div>
                </div>
            --}}
            <div class="col-xs-4 text-right">
                <div>{{ with($b = $payment->business)->name }}</div>
                <div>{{ $b->address1 }}</div>
                <div>{{ $b->address2 }}</div>
                <span>{{ $b->city }}</span>,
                <span>{{ $b->state }}</span>
                <span>{{ $b->zip }}</span>
                <div>{{ $b->phone1 }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <table class="table table-bordered mt-2">
                    <tbody>
                    <tr>
                        <th colspan="3"></th>
                        <th colspan="5">Rates</th>
                        {{--<th></th>--}}
                    </tr>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        {{--
                            <th>Activities Performed</th>
                        --}}
                        <th>Caregiver</th>
                        <th>Rate</th>
                        <th>Hours Type</th>
                        <th>Mileage</th>
                        <th>Hours</th>
                        <th>Total</th>
                    </tr>
                    @foreach($payment->shifts as $shift)
                        <tr >
                            <td>
                                {{ $shift->checked_in_time->format('m/d/Y') }}
                            </td>
                            <td>
                                {{ $shift->checked_in_time->format('g:i a') }} - {{ $shift->checked_out_time->format('g:i a') }}
                            </td>
                            {{--
                                <td>
                                    @foreach(collect($shift->activities)->unique()->sortBy('name') as $activity)
                                        <div>{{ $activity['name'] }}</div>
                                    @endforeach
                                </td>
                            --}}
                            <td>
                                {{ $shift->caregiver_name }}
                            </td>
                            <td>
                                ${{ $shift->hourly_total }}
                            </td>
                            <td>
                                {{ $shift->hours_type == 'default' ? 'regular' : $shift->hours_type }}
                            </td>
                            <td>
                                {{ $shift->mileage }}
                            </td>
                            <td>
                                {{ $shift->hours }}
                            </td>
                            <td>
                                <strong>
                                    &dollar;{{ $shift->shift_total }}
                                </strong>
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="6"></td>
                        <td>
                            <strong>
                                Total:
                            </strong>
                        </td>
                        <td>
                            <strong>
                                &dollar;{{ number_format($payment->shifts->sum('shift_total'), 2) }}
                            </strong>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection