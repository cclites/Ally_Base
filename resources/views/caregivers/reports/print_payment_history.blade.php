@extends('layouts.print')

@section('title')
    Payment History
@endsection

@section('content')
    <div class="container">
        <div class="row" style="margin-top: 1rem;">
            <div class="col-sm-4">
                {{ $caregiver->firstname }} {{ $caregiver->lastname }}<br>
                {{ optional($caregiver->user->addresses->first())->address1 }}<br>
                @if(optional($caregiver->user->addresses->first())->address2)
                    {{ optional($caregiver->user->addresses->first())->address2 }}<br>
                @endif
                {{ optional($caregiver->user->addresses->first())->city }}, {{ optional($caregiver->user->addresses->first())->state }} {{ optional($caregiver->user->addresses->first())->zip }}
            </div>
            <div class="col-sm-4">
                {{ $caregiver->businesses->first()->name }}<br>
                {{ $caregiver->businesses->first()->address1 }}<br>
                @if($caregiver->businesses->first()->address2)
                    {{ $caregiver->businesses->first()->address2 }}<br>
                @endif
                {{ $caregiver->businesses->first()->city }}, {{ $caregiver->businesses->first()->state }} {{ $caregiver->businesses->first()->zip }}
            </div>
            <div class="col-sm-4">
                <img src="{{ asset('images/AllyLogo-small.png') }}" alt="" style="width: 100px;">
            </div>
        </div>
        <table class="table" style="margin-top: 2rem;">
            <thead>
            <tr>
                <th>Paid</th>
                <th>Shifts Added</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>
            @foreach($deposits as $deposit)
                <tr>
                    <td>{{ $deposit->created_at->format('m/d/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($deposit->start)->format('m/d/Y') }} - {{ \Carbon\Carbon::parse($deposit->end)->format('m/d/Y') }}</td>
                    <td>&dollar;{{ number_format($deposit->amount, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <th>Total YTD</th>
                <th>{{ request()->year }}</th>
                <th>Total: &dollar;{{ number_format($deposits->sum('amount'), 2) }}</th>
            </tr>
            </tfoot>
        </table>
    </div>
@endsection