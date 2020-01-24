@extends('layouts.print')

@section('title')
    Payment History
@endsection

@section('content')
    @include('layouts.partials.print_logo')
    <div class="">
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
                {{ $business->name }}<br>
                {{ $business->address1 }}<br>
                @if($business->address2)
                    {{ $business->address2 }}<br>
                @endif
                {{ $business->city ? $business->city . ', ' : '' }} {{ $business->state }} {{ $business->zip }}
            </div>
            <div class="col-sm-4">
                <h2>Yearly Summary Report</h2>
            </div>
        </div>
        <table class="table" style="margin-top: 2rem;">
            <thead>
            <tr>
                <th>Paid</th>
                <th>Shifts Added</th>
                <th>Deposit Status</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>
            @foreach($deposits as $deposit)
                <tr>
                    <td>{{ $deposit->created_at->setTimezone($business->timezone)->format('m/d/Y') }}</td>
                    <td>
                        @if($deposit->adjustment)
                            Manual Adjustment
                        @else
                            {{ \Carbon\Carbon::parse($deposit->start)->format('m/d/Y') }} - {{ \Carbon\Carbon::parse($deposit->end)->format('m/d/Y') }}
                        @endif
                    </td>
                    <td>
                        @if ($deposit->success)
                            <span style="color: green">Completed</span>
                        @else
                            <span style="color: darkred">Failed/Returned</span>
                        @endif
                    </td>
                    <td>&dollar;{{ number_format($deposit->amount, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <th>Total YTD</th>
                <th>{{ request()->year }}</th>
                <th></th>
                <th>Total: &dollar;{{ number_format($deposits->where('success', 1)->sum('amount'), 2) }}</th>
            </tr>
            </tfoot>
        </table>
    </div>
@endsection
