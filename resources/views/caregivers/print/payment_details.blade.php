@extends('layouts.print')

@section('title', 'Payment Details - Print')

@push('head')
    <style>
        .header-left,
        .footer-left {
            float: left;
            width: 75%;
            padding-left: 0;
        }

        .footer-left {
            padding-left: 1rem;
        }

        .header-right,
        .footer-right {
            float: left;
            width: 25%;
            padding-right: 0;
        }

        .footer-right {
            padding-right: 1.2rem;
        }

        .header-right table tr td {
            padding-left: .5rem;
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
    <div>
        <div class="row print-header">
            <div class="header-left">
                <div><a href="{{ is_office_user() ? route('business.caregivers.show', [$deposit->caregiver]) . '#payment_statement' : route('caregiver.deposits') }}" class="btn btn-info">Return to Payment History</a></div>
                @include('layouts.partials.print_header')
                <div class="h4">Associated Provider: {{ $business->name }}</div>
                <div>{{ $business->address1 }}</div>
                @if($business->address2)
                    <div>{{ $business->address2 }}</div>
                @endif
                @if($business->city && $business->state)
                    <span>{{ $business->city }}</span>,
                    <span>{{ $business->state }}</span>
                @elseif($business->city)
                    {{ $busienss->city }}
                @elseif($business->state)
                    {{ $business->state }}
                @endif
                <span>{{ $business->zip }}</span>
                <div>{{ $business->phone1 }}</div>
            </div>
            <div class="text-right header-right">
                <div class="h4">Statement</div>
                <div>{{ $deposit->caregiver->name }}</div>
                <table style="float: right;">
                    <tr>
                        <td>Deposit Date</td>
                        <td>{{ $deposit->created_at->format('m/d/Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Deposit Status: </strong></td>
                        <td>
                            @if ($deposit->success)
                                <span style="color: green">Completed</span>
                            @else
                                <span style="color: darkred">Failed/Returned</span>
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
                        <th>Client (Employer)</th>
                        <th>Rate</th>
                        <th>Hours Type</th>
                        <th>Mileage</th>
                        <th>Hours</th>
                        <th>Total</th>
                    </tr>
                    @if($deposit->adjustment)
                        <tr>
                            <td>
                                {{ $deposit->created_at->setTimezone($business->timezone)->format('m/d/Y') }}
                            </td>
                            <td colspan="7">
                                {{ $deposit->notes }}
                            </td>
                            <td>
                                {{ $deposit->amount }}
                            </td>
                        </tr>
                    @endif
                    @foreach($shifts as $shift)
                        <tr >
                            <td>
                                {{ $shift->checked_in_time->setTimezone($business->timezone)->format('m/d/Y') }}
                            </td>
                            <td>
                                {{ $shift->checked_in_time->setTimezone($business->timezone)->format('g:i a') }} - {{ $shift->checked_out_time->setTimezone($business->timezone)->format('g:i a') }}
                            </td>
                            <td>
                                @foreach($shift->activities as $activity)
                                    <div>{{ $activity['name'] }}</div>
                                @endforeach
                            </td>
                            <td>
                                {{ $shift->client['masked_name'] }}
                            </td>
                            <td>
                                ${{ $shift->caregiver_rate }}
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
                                &dollar;{{ $shift->caregiver_total }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="footer-left">
                <p>This is a deposit statement.</p>
            </div>
            <div class="footer-right">
                <table class="table">
                    <tbody>
                    <tr>
                        <td>Total</td>
                        <td>
                            &dollar;{{ number_format($deposit->amount, 2) }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <p class="text-center">

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
