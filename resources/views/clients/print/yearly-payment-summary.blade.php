@extends('layouts.print')

@section('title')
    {{ $year }} Yearly Payment Summary
@endsection

@section('content')
    @include('layouts.partials.print_logo')
    <div class="">
        <div class="row" style="margin-top: 1rem;">
            <div class="col-sm-4">
                {{ $client->firstname }} {{ $client->lastname }}<br>
                {{ optional($client->user->addresses->first())->address1 }}<br>
                @if(optional($client->user->addresses->first())->address2)
                    {{ optional($client->user->addresses->first())->address2 }}<br>
                @endif
                {{ optional($client->user->addresses->first())->city }}, {{ optional($client->user->addresses->first())->state }} {{ optional($client->user->addresses->first())->zip }}
            </div>
            <div class="col-sm-4">
                {{ $client->business->name }}<br>
                {{ $client->business->address1 }}<br>
                @if($client->business->address2)
                    {{ $client->business->address2 }}<br>
                @endif
                {{ $client->business->city ? $client->business->city . ', ' : '' }} {{ $client->business->state }} {{ $client->business->zip }}
            </div>
            <div class="col-sm-4">
                <h2>{{ $year }} Yearly Payment Summary</h2>
            </div>
        </div>
        <table class="table" style="margin-top: 2rem;">
            <thead>
            <tr>
                <th>Paid</th>
                <th>Related Invoice(s)</th>
                <th>Payment Status</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>
            @foreach($payments as $payment)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($payment['payment_date'])->setTimezone($client->business->timezone)->format('m/d/Y') }}</td>
                    <td>{{ $payment['invoices']->pluck('name')->implode(', ') }}</td>
                    <td>
                        @if ($payment['status'])
                            <span style="color: green">Completed</span>
                        @else
                            <span style="color: darkred">Failed/Returned</span>
                        @endif
                    </td>
                    <td>&dollar;{{ number_format($payment['amount'], 2) }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <th>Total for {{ $year }}</th>
                <th></th>
                <th></th>
                <th>&dollar;{{ number_format($total, 2) }}</th>
            </tr>
            </tfoot>
        </table>
    </div>
@endsection
