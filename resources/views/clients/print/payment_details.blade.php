@extends('layouts.print')

@section('title', '$payment Details - Print')

@section('content')
    <div class="container-fluid">
        <div class="row pt-5">
            <div class="col">
                <div>{{ $payment->business->name }}</div>
                <div>{{ $payment->business->address1 }}</div>
                <div>{{ $payment->business->address2 }}</div>
                <span>{{ $payment->business->city }}</span>,
                <span>{{ $payment->business->state }}</span>
                <span>{{ $payment->business->zip }}</span>
                <div>{{ $payment->business->phone1 }}</div>
            </div>
            <div class="col">
                <div>Care Services Statement For:</div>
                <div>{{ $payment->client->name }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <table class="table table-bordered mt-2">
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
                    @foreach($payment->shifts as $shift)
                        <tr >
                            <td>
                                {{ $shift->checked_in_time->format('m/d/Y') }}
                            </td>
                            <td>
                                {{ $shift->checked_in_time->format('g:i a') }} - {{ $shift->checked_out_time->format('g:i a') }}
                            </td>
                            <td>
                                @foreach(collect($shift->activities)->unique()->sortBy('name') as $activity)
                                    <div>{{ $activity['name'] }}</div>
                                @endforeach
                            </td>
                            <td>
                                {{ $shift->caregiver['name'] }}
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
                                {{ $shift->duration }}
                            </td>
                            <td>
                                &dollar;{{ $shift->shift_total }}
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="7"></td>
                        <td>
                            Total:
                        </td>
                        <td>
                            &dollar;{{ number_format($payment->shifts->sum('shift_total'), 2) }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection